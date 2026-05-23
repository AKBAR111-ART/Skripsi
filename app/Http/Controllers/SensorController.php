<?php
    namespace App\Http\Controllers;

    use App\Models\Sensor;
    use App\Models\SensorRealtime;
    use App\Models\RuleSensor;
    use App\Models\SensorCalibration;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\Log;
    use Carbon\Carbon;

    class SensorController extends Controller
    {
        private $BATCH_SIZE = 10;
        
        /**
         * Terima data dari ESP32
         * POST /api/sensor/data
         */
        public function store(Request $request)
        {
            try {
                Log::info('Sensor data received:', $request->all());
                
                $validated = $request->validate([
                    'ph' => 'nullable|numeric|min:0|max:14',
                    'turbidity' => 'nullable|numeric|min:0|max:1000',
                ]);
                
                $ph = $validated['ph'] ?? null;
                $turbidity = $validated['turbidity'] ?? null;
                
                $rule = $this->getRuleFromCache();
                
                $statusPh = $this->getPhStatus($ph, $rule);
                $statusTurbidity = $this->getTurbidityStatus($turbidity, $rule);
                
                $this->addToBuffer([
                    'ph' => $ph,
                    'status_ph' => $statusPh,
                    'turbidity' => $turbidity,
                    'status_turbidity' => $statusTurbidity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->updateRealtime($ph, $statusPh, $turbidity, $statusTurbidity);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Data sensor diterima',
                    'data' => [
                        'ph' => $ph,
                        'ph_status' => $statusPh,
                        'turbidity' => $turbidity,
                        'turbidity_status' => $statusTurbidity
                    ]
                ], 201);
                
            } catch (\Exception $e) {
                Log::error('Sensor store error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ], 500);
            }
        }
        
        /**
         * ENDPOINT UNTUK ESP32 MENGAMBIL PERINTAH (GET COMMAND)
         * GET /api/sensor/command
         */
        public function getCommand(Request $request)
        {
            try {
                Log::info('ESP32 requesting command:', $request->all());
                
                $deviceId = $request->query('device_id', 'unknown');
                $lastCommand = $request->query('last_command', null);
                
                $realtime = DB::table('sensor_realtime')->first();
                $rule = $this->getRuleFromCache();
                $feedingRecommendation = $this->calculateFeedingCommand($realtime, $rule);
                
                $command = [
                    'action' => 'read_sensor',
                    'interval' => 30,
                    'relay_1' => false,
                    'relay_2' => false,
                    'auto_mode' => true,
                    'feeding' => $feedingRecommendation
                ];
                
                if ($realtime) {
                    $phStatus = $this->getPhStatus($realtime->ph, $rule);
                    $turbidityStatus = $this->getTurbidityStatus($realtime->turbidity, $rule);
                    
                    if ($phStatus === 'bahaya' || $turbidityStatus === 'bahaya') {
                        $command['action'] = 'warning';
                        $command['warning_message'] = 'Kualitas air berbahaya! Periksa kolam.';
                        $command['relay_1'] = true;
                    } elseif ($phStatus === 'peringatan' || $turbidityStatus === 'peringatan') {
                        $command['action'] = 'caution';
                        $command['caution_message'] = 'Kualitas air kurang baik.';
                    }
                }
                
                Log::info('Command sent to ESP32 (' . $deviceId . '):', $command);
                
                return response()->json([
                    'success' => true,
                    'command' => $command,
                    'timestamp' => now()->toIso8601String(),
                    'device_id' => $deviceId
                ], 200);
                
            } catch (\Exception $e) {
                Log::error('Get command error: ' . $e->getMessage());
                
                return response()->json([
                    'success' => false,
                    'command' => [
                        'action' => 'read_sensor',
                        'interval' => 60
                    ],
                    'error' => $e->getMessage()
                ], 500);
            }
        }
        
        /**
         * Helper untuk menghitung rekomendasi pakan dalam bentuk command
         */
        private function calculateFeedingCommand($realtime, $rule)
        {
            if (!$realtime) {
                return [
                    'recommended' => false,
                    'amount_kg' => 0,
                    'amount_gram' => 0
                ];
            }
            
            $phStatus = $this->getPhStatus($realtime->ph, $rule);
            $turbidityStatus = $this->getTurbidityStatus($realtime->turbidity, $rule);
            
            $canFeed = ($phStatus !== 'bahaya' && $turbidityStatus !== 'bahaya');
            
            return [
                'recommended' => $canFeed,
                'ph_status' => $phStatus,
                'turbidity_status' => $turbidityStatus,
                'amount_kg' => $canFeed ? 0.5 : 0,
                'amount_gram' => $canFeed ? 500 : 0
            ];
        }
        
        private function getRuleFromCache()
        {
            Cache::forget('sensor_rule');
            
            return Cache::remember('sensor_rule', 600, function () {
                return RuleSensor::first();
            });
        }
        
        public function clearRuleCache()
        {
            Cache::forget('sensor_rule');
            Cache::forget('sensor_realtime_display');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache rule sensor berhasil dibersihkan'
            ]);
        }
        

private function addToBuffer($data)
{
    $buffer = Cache::get('sensor_batch_buffer', []);
    $buffer[] = $data;
    
    // 🔥 IDEAL: Kombinasi jumlah + waktu
    $lastFlush = Cache::get('sensor_last_flush', now());
    
    // Insert jika sudah 10 data ATAU sudah 1 menit
    if (count($buffer) >= $this->BATCH_SIZE || $lastFlush->diffInSeconds(now()) >= 60) {
        DB::table('sensors')->insert($buffer);
        Cache::forget('sensor_batch_buffer');
        Cache::put('sensor_last_flush', now());
        Log::info('Batch inserted: ' . count($buffer) . ' records');
    } else {
        Cache::put('sensor_batch_buffer', $buffer, now()->addMinutes(2));
    }
}
        
        private function updateRealtime($ph, $statusPh, $turbidity, $statusTurbidity)
        {
            Log::info('Updating realtime with:', [
                'ph' => $ph,
                'statusPh' => $statusPh,
                'turbidity' => $turbidity,
                'statusTurbidity' => $statusTurbidity
            ]);
            
            $exists = DB::table('sensor_realtime')->exists();
            
            if ($exists) {
                DB::table('sensor_realtime')->update([
                    'ph' => $ph,
                    'ph_status' => $statusPh,
                    'turbidity' => $turbidity,
                    'turbidity_status' => $statusTurbidity,
                    'updated_at' => now()
                ]);
                Log::info('Updated existing realtime record');
            } else {
                DB::table('sensor_realtime')->insert([
                    'id' => 1,
                    'ph' => $ph,
                    'ph_status' => $statusPh,
                    'turbidity' => $turbidity,
                    'turbidity_status' => $statusTurbidity,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                Log::info('Created new realtime record');
            }
            
            Cache::put('sensor_realtime_display', [
                'ph' => (float)$ph,
                'ph_status' => $statusPh,
                'turbidity' => (float)$turbidity,
                'turbidity_status' => $statusTurbidity,
                'last_update' => now()
            ], now()->addSeconds(5));
        }
        
        public function latest()
        {
            return $this->realtime();
        }
        
        public function history(Request $request)
        {
            $limit = $request->get('limit', 24);
            $sensors = DB::table('sensors')
                ->latest()
                ->limit($limit)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $sensors
            ]);
        }
        
        public function getFeedingRecommendation()
        {
            try {
                $sensor = DB::table('sensor_realtime')->first();
                $profile = DB::table('tambak_profile')->first();
                
                if (!$profile || !$profile->populasi) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data populasi belum diisi di halaman Profile'
                    ], 400);
                }
                
                $umurMinggu = 1;
                if ($profile->tanggal_mulai_budidaya) {
                    $start = Carbon::parse($profile->tanggal_mulai_budidaya);
                    $umurHari = $start->diffInDays(now());
                    $umurMinggu = max(1, ceil($umurHari / 7));
                }
                
                $pakanPerEkor = $this->getPakanPerEkor($umurMinggu);
                $populasi = $profile->populasi;
                $pakanDasarGram = $populasi * $pakanPerEkor;
                $pakanDasarGram = max(50, min($pakanDasarGram, 10000));
                
                $rule = $this->getRuleFromCache();
                $calibratedPh = SensorCalibration::getCalibratedValue($sensor->ph ?? 7, 'ph');
                $calibratedTurbidity = SensorCalibration::getCalibratedValue($sensor->turbidity ?? 30, 'turbidity');
                
                $phStatus = $this->getPhStatus($calibratedPh, $rule);
                $turbidityStatus = $this->getTurbidityStatus($calibratedTurbidity, $rule);
                
                $faktorPakan = 1.0;
                $statusKeseluruhan = 'aman';
                
                if ($phStatus === 'bahaya' || $turbidityStatus === 'bahaya') {
                    $faktorPakan = 0;
                    $statusKeseluruhan = 'bahaya';
                } elseif ($phStatus === 'peringatan' || $turbidityStatus === 'peringatan') {
                    $faktorPakan = 0.5;
                    $statusKeseluruhan = 'peringatan';
                }
                
                $pakanRekomendasiGram = round($pakanDasarGram * $faktorPakan);
                $pakanRekomendasiKg = round($pakanRekomendasiGram / 1000, 2);
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'umur_minggu' => $umurMinggu,
                        'populasi' => $populasi,
                        'pakan_per_ekor' => $pakanPerEkor,
                        'pakan_dasar_gram' => (int)$pakanDasarGram,
                        'ph' => (float)$calibratedPh,
                        'ph_status' => $phStatus,
                        'turbidity' => (float)$calibratedTurbidity,
                        'turbidity_status' => $turbidityStatus,
                        'status_keseluruhan' => $statusKeseluruhan,
                        'faktor_pakan' => $faktorPakan,
                        'pakan_rekomendasi_gram' => $pakanRekomendasiGram,
                        'pakan_rekomendasi_kg' => $pakanRekomendasiKg,
                        'pakan_rekomendasi_text' => $pakanRekomendasiKg . ' kg (' . number_format($pakanRekomendasiGram) . ' gram)',
                        'keterangan' => $this->getKeterangan($statusKeseluruhan)
                    ]
                ]);
                
            } catch (\Exception $e) {
                Log::error('Get feeding recommendation error: ' . $e->getMessage());
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'pakan_rekomendasi_gram' => 500,
                        'pakan_rekomendasi_kg' => 0.5,
                        'umur_minggu' => 2,
                        'populasi' => 5000,
                        'ph' => 7.0,
                        'ph_status' => 'normal',
                        'turbidity' => 50,
                        'turbidity_status' => 'normal',
                        'status_keseluruhan' => 'normal',
                        'keterangan' => '✅ Data default'
                    ]
                ]);
            }
        }
        
        private function getPakanPerEkor($umurMinggu)
        {
            $tabelPakan = [
                1  => 0.5, 2  => 1.0, 3  => 2.0, 4  => 3.0,
                5  => 4.5, 6  => 6.0, 7  => 8.0, 8  => 10.0,
                9  => 12.5, 10 => 15.0, 11 => 18.0, 12 => 21.0, 13 => 25.0,
            ];
            
            if (isset($tabelPakan[$umurMinggu])) {
                return $tabelPakan[$umurMinggu];
            }
            
            if ($umurMinggu > 13) {
                $mingguTambahan = $umurMinggu - 13;
                return 25.0 + ($mingguTambahan * 3.5);
            }
            
            return 0.5;
        }
        
        private function getKeterangan($status)
        {
            switch ($status) {
                case 'bahaya':
                    return '⚠️ KONDISI BERBAHAYA! Hentikan pemberian pakan sementara.';
                case 'peringatan':
                    return '⚠️ Kualitas air kurang baik. Kurangi pakan 50%.';
                default:
                    return '✅ Kualitas air optimal. Berikan pakan sesuai jadwal.';
            }
        }
        
        private function getPhStatus($ph, $rule = null)
        {
            if (!$rule) return 'aman';
            
            if ($ph < $rule->ph_danger_low || $ph > $rule->ph_danger_high) {
                return 'bahaya';
            }
            
            if ($ph < $rule->ph_min_good || $ph > $rule->ph_max_good) {
                return 'peringatan';
            }
            
            return 'aman';
        }
        
        private function getTurbidityStatus($turbidity, $rule = null)
        {
            if ($turbidity === null) return 'baik';
            
            if ($turbidity < 0 || $turbidity > 1000) {
                Log::warning('Invalid turbidity value: ' . $turbidity);
                return 'baik';
            }
            
            if (!$rule) {
                if ($turbidity > 100) return 'bahaya';
                if ($turbidity > 50) return 'peringatan';
                return 'baik';
            }
            
            if ($turbidity > $rule->turbidity_danger_high) {
                return 'bahaya';
            }
            
            if ($turbidity > $rule->turbidity_max_good) {
                return 'peringatan';
            }
            
            return 'baik';
        }
        
        public function realtime()
        {
            $cached = Cache::get('sensor_realtime_display');
            if ($cached) {
                return response()->json($cached);
            }
            
            $sensor = DB::table('sensor_realtime')->first();
            $rule = RuleSensor::first();
            
            if (!$sensor) {
                return response()->json([
                    'ph' => 7.0,
                    'ph_status' => 'aman',
                    'turbidity' => 30,
                    'turbidity_status' => 'aman'
                ]);
            }
            
            $rawPh = $sensor->ph;
            $rawTurbidity = $sensor->turbidity;
            
            $calibratedPh = SensorCalibration::getCalibratedValue($rawPh, 'ph');
            $calibratedTurbidity = SensorCalibration::getCalibratedValue($rawTurbidity, 'turbidity');
            
            $phStatus = $this->getPhStatus($calibratedPh, $rule);
            $turbidityStatus = $this->getTurbidityStatus($calibratedTurbidity, $rule);
            
            $calibration = SensorCalibration::getCalibration();
            
            return response()->json([
                'ph' => (float)$calibratedPh,
                'ph_raw' => (float)$rawPh,
                'ph_status' => $phStatus,
                'turbidity' => (float)$calibratedTurbidity,
                'turbidity_raw' => (float)$rawTurbidity,
                'turbidity_status' => $turbidityStatus,
                'is_calibrated' => $calibration->is_calibrated,
                'noise_level' => $calibration->noise_level,
                'last_calibration' => $calibration->last_calibration,
                'last_update' => $sensor->updated_at ?? $sensor->created_at
            ]);
        }
        
        /**
         * Kirim perintah pakan
         * POST /api/sensor/send-feed-command
         */
      public function sendFeedCommand(Request $request)
{
    try {
        Log::info('Send feed command received:', $request->all());
        
        // Ambil parameter (dalam GRAM dari JS, atau KG dari form)
        $pakanGram = $request->pakan_gram ?? $request->pakan ?? $request->amount_gram ?? null;
        
        if (!$pakanGram) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pakan tidak ditemukan.'
            ], 400);
        }
        
        $pakanGram = (int)$pakanGram;
        $pakanKg = round($pakanGram / 1000, 2);
        
        // Validasi
        if ($pakanGram < 1 || $pakanGram > 10000) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pakan harus antara 1-10000 gram'
            ], 422);
        }
        
        $jadwal = $request->jadwal ?? $this->getCurrentSchedule();
        $sumber = $request->sumber ?? 'manual';
        
        // 🔥 SIMPAN: pakan_kg (kg) dan target_gram (gram)
        DB::table('feeding_records')->insert([
            'pakan_kg' => $pakanKg,
            'target_gram' => $pakanGram,  // ← konversi ke gram
            'jadwal' => $jadwal,
            'sumber' => $sumber,
            'status' => 'success',
            'tanggal' => now()->toDateString(),
            'waktu_pemberian' => now()->toTimeString(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "✅ Pakan {$pakanGram} gram ({$pakanKg} kg) berhasil dikirim"
        ]);
        
    } catch (\Exception $e) {
        Log::error('Send feed command error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim pakan: ' . $e->getMessage()
        ], 500);
    }
}
        
        private function getCurrentSchedule()
        {
            $hour = now()->hour;
            if ($hour >= 5 && $hour < 11) {
                return 'pagi';
            } elseif ($hour >= 11 && $hour < 15) {
                return 'siang';
            }
            return 'sore';
        }
        
        public function getWeeklyFeedData()
        {
            try {
                $feedData = DB::table('feeding_records')
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(pakan_gram) as total_gram'))
                    ->where('created_at', '>=', now()->subDays(7))
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get();
                
                $days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                $weeklyData = array_fill(0, 7, 0);
                
                $dayMap = [
                    'Monday' => 'Sen', 'Tuesday' => 'Sel', 'Wednesday' => 'Rab',
                    'Thursday' => 'Kam', 'Friday' => 'Jum', 'Saturday' => 'Sab', 'Sunday' => 'Min'
                ];
                
                foreach ($feedData as $data) {
                    $date = Carbon::parse($data->date);
                    $dayName = $dayMap[$date->format('l')];
                    $index = array_search($dayName, $days);
                    if ($index !== false) {
                        $weeklyData[$index] = (int)$data->total_gram;
                    }
                }
                
                $hasRealData = $feedData->isNotEmpty();
                
                return response()->json([
                    'success' => true,
                    'labels' => $days,
                    'data' => $weeklyData,
                    'has_real_data' => $hasRealData
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error getting weekly feed data: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'labels' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                    'data' => [0, 0, 0, 0, 0, 0, 0]
                ], 500);
            }
        }
        
        public function calibratePh(Request $request)
        {
            try {
                $request->validate([
                    'desired_value' => 'required|numeric|min:0|max:14',
                    'current_value' => 'required|numeric'
                ]);
                
                $calibration = SensorCalibration::setCalibration(
                    'ph', 
                    $request->desired_value, 
                    $request->current_value
                );
                
                Log::info('pH Calibrated', [
                    'desired' => $request->desired_value,
                    'current' => $request->current_value,
                    'offset' => $calibration->ph_offset
                ]);
                
                Cache::forget('sensor_realtime_display');
                Cache::forget('sensor_rule');
                
                return response()->json([
                    'success' => true,
                    'message' => '✅ pH berhasil dikalibrasi',
                    'data' => $calibration
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal kalibrasi pH: ' . $e->getMessage()
                ], 500);
            }
        }
        
        public function calibrateTurbidity(Request $request)
        {
            try {
                $request->validate([
                    'desired_value' => 'required|numeric|min:0|max:1000',
                    'current_value' => 'required|numeric'
                ]);
                
                $calibration = SensorCalibration::setCalibration(
                    'turbidity', 
                    $request->desired_value, 
                    $request->current_value
                );
                
                Log::info('Turbidity Calibrated', [
                    'desired' => $request->desired_value,
                    'current' => $request->current_value,
                    'offset' => $calibration->turbidity_offset
                ]);
                
                Cache::forget('sensor_realtime_display');
                Cache::forget('sensor_rule');
                
                return response()->json([
                    'success' => true,
                    'message' => '✅ Turbidity berhasil dikalibrasi',
                    'data' => $calibration
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal kalibrasi turbidity: ' . $e->getMessage()
                ], 500);
            }
        }
        
        public function resetCalibration(Request $request)
        {
            try {
                $type = $request->type;
                $calibration = SensorCalibration::resetCalibration($type);
                
                Cache::forget('sensor_realtime_display');
                Cache::forget('sensor_rule');
                
                return response()->json([
                    'success' => true,
                    'message' => '✅ Kalibrasi ' . $type . ' direset ke default',
                    'data' => $calibration
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal reset kalibrasi: ' . $e->getMessage()
                ], 500);
            }
        }
        
        public function getCalibrationStatus()
        {
            $calib = SensorCalibration::getCalibration();
            $sensor = DB::table('sensor_realtime')->first();
            
            $noiseWarning = null;
            if ($calib->noise_level === 'tinggi') {
                $noiseWarning = '⚠️ Noise sensor tinggi! Segera bersihkan atau kalibrasi ulang sensor.';
            } elseif ($calib->noise_level === 'sedang') {
                $noiseWarning = '⚠️ Noise sensor sedang. Pertimbangkan untuk membersihkan sensor.';
            }
            
            return response()->json([
                'success' => true,
                'ph_offset' => $calib->ph_offset,
                'turbidity_offset' => $calib->turbidity_offset,
                'is_calibrated' => $calib->is_calibrated,
                'last_calibration' => $calib->last_calibration,
                'noise_level' => $calib->noise_level,
                'noise_warning' => $noiseWarning,
                'current_ph' => $sensor->ph ?? 7.0,
                'current_turbidity' => $sensor->turbidity ?? 30
            ]);
        }
    }