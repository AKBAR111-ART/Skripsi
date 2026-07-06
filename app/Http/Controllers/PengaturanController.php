<?php

namespace App\Http\Controllers;

use App\Models\PengaturanTambak;
use App\Models\RuleSensor;
use App\Models\JadwalPengingat;
use App\Services\WeatherService;
use App\Traits\FeedingCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\TambakProfile; 

class PengaturanController extends Controller
{
    use FeedingCalculator;
    
    protected $weatherService;

    /**
     * Constructor with dependency injection
     */
    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Halaman utama pengaturan
     */
    public function index()
    {
        $pengaturan = PengaturanTambak::first();
        $rule = RuleSensor::first();
        
        $jadwalList = DB::table('jadwal_pengingat')
            ->orderBy('jam', 'asc')
            ->get();
        
        foreach ($jadwalList as $item) {
            $target = json_decode($item->target_nomor, true);
            $item->target_nomor_formatted = is_array($target) ? implode(', ', $target) : $item->target_nomor;
        }
        
        if (!$rule) {
            $rule = RuleSensor::create([
                'ph_min_good' => 7.5,
                'ph_max_good' => 8.5,
                'ph_min_warning' => 7.0,
                'ph_max_warning' => 9.0,
                'ph_min_warning_high' => 8.6,
                'ph_max_warning_high' => 8.9,
                'ph_danger_low' => 6.5,
                'ph_danger_high' => 9.5,
                'turbidity_min_good' => 0,
                'turbidity_max_good' => 300,
                'turbidity_min_warning' => 301,
                'turbidity_max_warning' => 700,
                'turbidity_danger_low' => 0,
                'turbidity_danger_high' => 700,
            ]);
        }
        
        return view('dashboard.pengaturan', compact('pengaturan', 'rule', 'jadwalList'));
    }
    
    /**
     * Store pengaturan umum
     */
    public function store(Request $request)
    {
        try {
            $pengaturan = PengaturanTambak::first();
            
            if (!$pengaturan) {
                $pengaturan = new PengaturanTambak();
            }
            
            if ($request->has('pengingat')) {
                $pengingat = $request->pengingat;
                $pengaturan->penjaga = json_encode($pengingat['penjaga'] ?? []);
                $pengaturan->nomor_wa = json_encode($pengingat['wa'] ?? []);
                $pengaturan->waktu = json_encode($pengingat['waktu'] ?? []);
                $pengaturan->tanggal = $pengingat['tanggal'] ?? null;
                $pengaturan->template_pesan = $pengingat['template_pesan'] ?? '';
            }
            
            $pengaturan->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan berhasil disimpan'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error saving pengaturan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * UPDATE RULE SENSOR (pH + Turbidity NTU) - dengan clear cache sensor
     * 🔥 LANGSUNG UPDATE STATUS SENSOR SETELAH RULE BERUBAH
     */
    public function updateRule(Request $request)
    {
        try {
            $rule = RuleSensor::first();
            
            if (!$rule) {
                $rule = new RuleSensor();
            }
            
            // ==================== pH Rules ====================
            $rule->ph_min_good = $request->ph_min_good;
            $rule->ph_max_good = $request->ph_max_good;
            $rule->ph_min_warning = $request->ph_min_warning;
            $rule->ph_max_warning = $request->ph_max_warning;
            $rule->ph_min_warning_high = $request->ph_min_warning_high;
            $rule->ph_max_warning_high = $request->ph_max_warning_high;
            $rule->ph_danger_low = $request->ph_danger_low;
            $rule->ph_danger_high = $request->ph_danger_high;
            
            // ==================== Turbidity Rules (NTU) ====================
            $rule->turbidity_min_good = $request->turbidity_min_good ?? 0;
            $rule->turbidity_max_good = $request->turbidity_max_good ?? 300;
            $rule->turbidity_min_warning = $request->turbidity_min_warning ?? 301;
            $rule->turbidity_max_warning = $request->turbidity_max_warning ?? 700;
            $rule->turbidity_danger_low = $request->turbidity_danger_low ?? 0;
            $rule->turbidity_danger_high = $request->turbidity_danger_high ?? 700;
            
            $rule->save();
            
            // 🔥 UPDATE STATUS SENSOR BERDASARKAN RULE BARU
            $this->updateSensorStatus();
            
            // Clear cache
            Cache::forget('sensor_rule');
            Cache::forget('sensor_realtime_display');
            
            Log::info('Rule updated and sensor status refreshed', [
                'ph_min_good' => $rule->ph_min_good,
                'ph_max_good' => $rule->ph_max_good,
                'ph_min_warning' => $rule->ph_min_warning,
                'ph_max_warning' => $rule->ph_max_warning
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '✅ Rule sensor berhasil disimpan dan status sensor diperbarui'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error saving rule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan rule: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * RESET RULE SENSOR KE DEFAULT
     * 🔥 LANGSUNG UPDATE STATUS SENSOR SETELAH RULE DIRESET
     */
    public function resetRule()
    {
        try {
            $rule = RuleSensor::first();
            
            if (!$rule) {
                $rule = new RuleSensor();
            }
            
            // Reset ke default
            $rule->ph_min_good = 7.0;
            $rule->ph_max_good = 8.5;
            $rule->ph_min_warning = 6.5;
            $rule->ph_max_warning = 7.0;
            $rule->ph_min_warning_high = 8.5;
            $rule->ph_max_warning_high = 9.0;
            $rule->ph_danger_low = 6.0;
            $rule->ph_danger_high = 9.5;
            
            $rule->turbidity_min_good = 0;
            $rule->turbidity_max_good = 50;
            $rule->turbidity_min_warning = 51;
            $rule->turbidity_max_warning = 100;
            $rule->turbidity_danger_low = 0;
            $rule->turbidity_danger_high = 100;
            
            $rule->save();
            
            // 🔥 UPDATE STATUS SENSOR BERDASARKAN RULE BARU
            $this->updateSensorStatus();
            
            Cache::forget('sensor_rule');
            Cache::forget('sensor_realtime_display');
            
            Log::info('Rule reset to default and sensor status refreshed');
            
            return response()->json([
                'success' => true,
                'message' => '✅ Rule berhasil direset dan status sensor diperbarui'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error reset rule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal reset: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * GET LATEST RULE (dengan clear cache)
     */
    public function getLatestRule()
    {
        Cache::forget('sensor_rule');
        
        $rule = RuleSensor::first();
        
        if (!$rule) {
            $rule = RuleSensor::create([
                'ph_min_good' => 7.5,
                'ph_max_good' => 8.5,
                'ph_min_warning' => 7.0,
                'ph_max_warning' => 9.0,
                'ph_min_warning_high' => 8.6,
                'ph_max_warning_high' => 8.9,
                'ph_danger_low' => 6.5,
                'ph_danger_high' => 9.5,
                'turbidity_min_good' => 0,
                'turbidity_max_good' => 300,
                'turbidity_min_warning' => 301,
                'turbidity_max_warning' => 700,
                'turbidity_danger_low' => 0,
                'turbidity_danger_high' => 700,
            ]);
        }
        
        return response()->json($rule);
    }
    
    /**
     * API: Get all jadwal pengingat
     */
    public function getJadwalList()
    {
        try {
            $jadwal = DB::table('jadwal_pengingat')
                ->orderBy('jam', 'asc')
                ->get();
            
            foreach ($jadwal as $item) {
                $target = json_decode($item->target_nomor, true);
                if (is_array($target)) {
                    $item->target_nomor_formatted = implode(', ', $target);
                } else {
                    $item->target_nomor_formatted = $item->target_nomor;
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $jadwal
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get jadwal list error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Format nomor untuk ditampilkan
     */
    private function formatNomor($nomor)
    {
        if (is_array($nomor)) {
            return implode(', ', $nomor);
        }
        return $nomor;
    }
    
    /**
     * API: Store new jadwal pengingat
     */
    public function storeJadwal(Request $request)
    {
        try {
            Log::info('Store jadwal request:', $request->all());
            
            $validated = $request->validate([
                'jam' => 'required|string',
                'pesan' => 'required|string|max:500',
                'target_nomor' => 'nullable|array'
            ]);
            
            if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $validated['jam'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format jam harus HH:MM (contoh: 14:30)'
                ], 422);
            }
            
            $nomors = [];
            if ($request->has('target_nomor') && is_array($request->target_nomor)) {
                foreach ($request->target_nomor as $nomor) {
                    $nomor = preg_replace('/[^0-9]/', '', $nomor);
                    if (!str_starts_with($nomor, '62')) {
                        $nomor = '62' . ltrim($nomor, '0');
                    }
                    $nomors[] = $nomor;
                }
            }
            
            $jadwal = JadwalPengingat::create([
                'jam' => $validated['jam'],
                'pesan' => $validated['pesan'],
                'target_nomor' => $nomors,
                'is_sent' => false
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '✅ Jadwal berhasil ditambahkan',
                'data' => $jadwal
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . json_encode($e->errors())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Store jadwal error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah jadwal: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Delete jadwal pengingat
     */
    public function deleteJadwal($id)
    {
        try {
            $deleted = DB::table('jadwal_pengingat')->where('id', $id)->delete();
            
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal tidak ditemukan'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => '✅ Jadwal dihapus'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Delete jadwal error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal hapus jadwal: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Get realtime sensor untuk card monitoring (V1 - TETAP ADA)
     */
    public function getRealtimeSensor()
    {
        $sensor = DB::table('sensor_realtime')->first();
        $rule = RuleSensor::first();
        
        if (!$sensor) {
            return response()->json([
                'success' => true,
                'ph' => 7.0,
                'ph_status' => 'Normal',
                'turbidity' => 30,
                'turbidity_status' => 'Normal',
                'rekomendasi_kg' => 0.5,
                'last_update' => now()
            ]);
        }
        
        $rekomendasi = $this->hitungRekomendasiPakan($sensor->ph, $sensor->turbidity);
        
        return response()->json([
            'success' => true,
            'ph' => $sensor->ph,
            'ph_status' => $this->getStatusText($sensor->ph, $rule),
            'turbidity' => $sensor->turbidity,
            'turbidity_status' => $this->getTurbidityStatusText($sensor->turbidity, $rule),
            'rekomendasi_kg' => $rekomendasi,
            'last_update' => $sensor->updated_at ?? $sensor->created_at
        ]);
    }
    
    /**
     * API Get realtime sensor with Trait (STANDARISASI - V2)
     * Method ini KONSISTEN dengan HomeController
     */
    public function getRealtimeSensorV2()
    {
        $sensor = DB::table('sensor_realtime')->first();
        $profile = TambakProfile::first();
        $rule = RuleSensor::first();
        
        if (!$sensor) {
            return response()->json([
                'success' => true,
                'ph' => 7.0,
                'ph_status' => 'Normal',
                'turbidity' => 30,
                'turbidity_status' => 'Normal',
                'rekomendasi_kg' => 0.5,
                'last_update' => now()
            ]);
        }
        
        // 🔥 PAKAI TRAIT UNTUK PERHITUNGAN STANDAR
        $result = $this->calculateFeed($profile, (object) [
            'ph' => $sensor->ph,
            'turbidity' => $sensor->turbidity,
            'ph_status' => $sensor->ph_status ?? 'baik',
            'turbidity_status' => $sensor->turbidity_status ?? 'baik'
        ]);
        
        return response()->json([
            'success' => true,
            'ph' => $sensor->ph,
            'ph_status' => $this->getStatusText($sensor->ph, $rule),
            'turbidity' => $sensor->turbidity,
            'turbidity_status' => $this->getTurbidityStatusText($sensor->turbidity, $rule),
            'rekomendasi_kg' => $result['pakan_rekomendasi_kg'],
            'last_update' => $sensor->updated_at ?? $sensor->created_at
        ]);
    }
    
    /**
     * Hitung rekomendasi pakan berdasarkan kondisi air (V1 - TETAP ADA)
     */
    private function hitungRekomendasiPakan($ph, $turbidity)
    {
        $targetPakan = 500;
        
        $faktor = 1.0;
        
        if ($ph < 6.5 || $ph > 8.5 || $turbidity > 100) {
            $faktor = 0;
        } elseif ($ph < 7.0 || $ph > 8.0 || $turbidity > 50) {
            $faktor = 0.5;
        }
        
        return round(($targetPakan * $faktor) / 1000, 2);
    }
    
    /**
     * Get status text for pH (V1 - TETAP ADA)
     */
    private function getStatusText($ph, $rule = null)
    {
        if (!$rule) {
            if ($ph < 6.5 || $ph > 8.5) return 'Bahaya';
            if ($ph < 7.0 || $ph > 8.0) return 'Peringatan';
            return 'Normal';
        }
        
        if ($ph < $rule->ph_danger_low || $ph > $rule->ph_danger_high) return 'Bahaya';
        if ($ph < $rule->ph_min_good || $ph > $rule->ph_max_good) return 'Peringatan';
        return 'Normal';
    }
    
    /**
     * Get status text for turbidity (V1 - TETAP ADA)
     */
    private function getTurbidityStatusText($turbidity, $rule = null)
    {
        if (!$rule) {
            if ($turbidity > 100) return 'Bahaya';
            if ($turbidity > 50) return 'Peringatan';
            return 'Normal';
        }
        
        if ($turbidity > $rule->turbidity_danger_high) return 'Bahaya';
        if ($turbidity > $rule->turbidity_max_good) return 'Peringatan';
        return 'Normal';
    }
    
    /**
     * Update cuaca dan intensitas hujan
     */
    public function updateCuaca(Request $request)
    {
        try {
            $validated = $request->validate([
                'cuaca' => 'required|string|in:Cerah,Berawan,Hujan,Petir',
                'intensitas_hujan' => 'nullable|numeric|min:0|max:500'
            ]);
            
            $profile = TambakProfile::first();
            if (!$profile) {
                $profile = new TambakProfile();
            }
            
            $profile->cuaca = $validated['cuaca'];
            $profile->intensitas_hujan = $validated['intensitas_hujan'] ?? 0;
            $profile->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Data cuaca berhasil diupdate'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get data cuaca dari API eksternal (BMKG/OpenWeatherMap)
     */
    public function getCuacaFromApi()
    {
        try {
            $apiKey = env('OPENWEATHER_API_KEY', '');
            $city = env('WEATHER_CITY', 'Jakarta');
            
            if (empty($apiKey)) {
                $profile = TambakProfile::first();
                return response()->json([
                    'success' => true,
                    'data' => [
                        'cuaca' => $profile->cuaca ?? 'Cerah',
                        'intensitas_hujan' => $profile->intensitas_hujan ?? 0,
                        'sumber' => 'manual'
                    ]
                ]);
            }
            
            $url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$apiKey}&units=metric";
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            
            $weatherMain = $data['weather'][0]['main'] ?? 'Clear';
            $cuaca = 'Cerah';
            $intensitasHujan = 0;
            
            switch ($weatherMain) {
                case 'Rain':
                case 'Drizzle':
                    $cuaca = 'Hujan';
                    $intensitasHujan = $data['rain']['1h'] ?? 5;
                    break;
                case 'Thunderstorm':
                    $cuaca = 'Petir';
                    $intensitasHujan = $data['rain']['1h'] ?? 10;
                    break;
                case 'Clouds':
                    $cuaca = 'Berawan';
                    break;
                default:
                    $cuaca = 'Cerah';
            }
            
            $profile = TambakProfile::first();
            if ($profile) {
                $profile->cuaca = $cuaca;
                $profile->intensitas_hujan = $intensitasHujan;
                $profile->save();
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'cuaca' => $cuaca,
                    'intensitas_hujan' => $intensitasHujan,
                    'suhu' => $data['main']['temp'] ?? null,
                    'kelembaban' => $data['main']['humidity'] ?? null,
                    'sumber' => 'api'
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Weather API error: ' . $e->getMessage());
            
            $profile = TambakProfile::first();
            return response()->json([
                'success' => true,
                'data' => [
                    'cuaca' => $profile->cuaca ?? 'Cerah',
                    'intensitas_hujan' => $profile->intensitas_hujan ?? 0,
                    'sumber' => 'fallback'
                ]
            ]);
        }
    }
    
    /**
     * GET FEEDING RECOMMENDATION untuk card monitoring (V1 - TETAP ADA)
     */
    public function getFeedingRecommendation()
    {
        try {
            $sensor = DB::table('sensor_realtime')->first();
            $profile = TambakProfile::first();
            
            if (!$sensor) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'pakan_rekomendasi_kg' => 0.5,
                        'status_keseluruhan' => 'normal',
                        'keterangan' => 'Data sensor belum tersedia'
                    ]
                ]);
            }
            
            $biomassaKg = $profile->biomassa_kg ?? 0;
            $umurHari = $profile->umur_hari ?? 0;
            
            if ($umurHari <= 7) {
                $feedingRate = 0.10;
            } elseif ($umurHari <= 14) {
                $feedingRate = 0.08;
            } elseif ($umurHari <= 21) {
                $feedingRate = 0.07;
            } elseif ($umurHari <= 28) {
                $feedingRate = 0.06;
            } elseif ($umurHari <= 42) {
                $feedingRate = 0.05;
            } else {
                $feedingRate = 0.04;
            }
            
            $pakanDasarKg = round($biomassaKg * $feedingRate, 2);
            
            $rule = RuleSensor::first();
            $ph = $sensor->ph ?? 7;
            $turbidity = $sensor->turbidity ?? 30;
            
            $statusPh = $this->getStatusText($ph, $rule);
            $statusTurbidity = $this->getTurbidityStatusText($turbidity, $rule);
            
            $faktorAir = 1.0;
            $statusKeseluruhan = 'normal';
            
            if ($statusPh === 'Bahaya' || $statusTurbidity === 'Bahaya') {
                $faktorAir = 0;
                $statusKeseluruhan = 'bahaya';
            } elseif ($statusPh === 'Peringatan' || $statusTurbidity === 'Peringatan') {
                $faktorAir = 0.5;
                $statusKeseluruhan = 'peringatan';
            }
            
            $pakanRekomendasiKg = round($pakanDasarKg * $faktorAir, 2);
            
            $keterangan = $this->getKeteranganRekomendasi($statusKeseluruhan);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'pakan_rekomendasi_kg' => $pakanRekomendasiKg,
                    'pakan_rekomendasi_gram' => $pakanRekomendasiKg * 1000,
                    'pakan_dasar_kg' => $pakanDasarKg,
                    'status_keseluruhan' => $statusKeseluruhan,
                    'keterangan' => $keterangan,
                    'ph_status' => $statusPh,
                    'turbidity_status' => $statusTurbidity
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('getFeedingRecommendation error: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'data' => [
                    'pakan_rekomendasi_kg' => 0.5,
                    'status_keseluruhan' => 'normal',
                    'keterangan' => 'Data default'
                ]
            ]);
        }
    }

    /**
     * GET FEEDING RECOMMENDATION with Trait (STANDARISASI - V2)
     * Method ini KONSISTEN dengan HomeController
     */
    public function getFeedingRecommendationV2()
    {
        try {
            $sensor = DB::table('sensor_realtime')->first();
            $profile = TambakProfile::first();
            
            if (!$sensor) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'pakan_rekomendasi_kg' => 0.5,
                        'status_keseluruhan' => 'normal',
                        'keterangan' => 'Data sensor belum tersedia'
                    ]
                ]);
            }
            
            // 🔥 PAKAI TRAIT UNTUK PERHITUNGAN STANDAR
            $result = $this->calculateFeed($profile, (object) [
                'ph' => $sensor->ph,
                'turbidity' => $sensor->turbidity,
                'ph_status' => $sensor->ph_status ?? 'baik',
                'turbidity_status' => $sensor->turbidity_status ?? 'baik'
            ]);
            
            $result['ph_status'] = $this->getStatusText($sensor->ph ?? 7, null);
            $result['turbidity_status'] = $this->getTurbidityStatusText($sensor->turbidity ?? 30, null);
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('getFeedingRecommendationV2 error: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'data' => [
                    'pakan_rekomendasi_kg' => 0.5,
                    'status_keseluruhan' => 'normal',
                    'keterangan' => 'Data default'
                ]
            ]);
        }
    }

    /**
     * Get keterangan rekomendasi
     */
    private function getKeteranganRekomendasi($status)
    {
        switch ($status) {
            case 'bahaya':
                return '🔴 KONDISI BAHAYA! Hentikan pemberian pakan sementara.';
            case 'peringatan':
                return '⚠️ Kualitas air kurang baik. Kurangi pakan 50%.';
            default:
                return '✅ Kualitas air optimal. Berikan pakan sesuai jadwal.';
        }
    }

    // ==================== 🔥 METHOD BARU UNTUK UPDATE STATUS SENSOR ====================

    /**
     * 🔥 UPDATE STATUS SENSOR BERDASARKAN RULE TERBARU
     * Method ini dipanggil saat rule diubah atau direset
     */
    private function updateSensorStatus()
    {
        $sensor = DB::table('sensor_realtime')->first();
        if (!$sensor) {
            return;
        }
        
        $rule = RuleSensor::first();
        if (!$rule) {
            return;
        }
        
        // 🔥 Ambil nilai yang sudah dikalibrasi (dengan offset)
        $calib = DB::table('sensor_calibration')->first();
        $offset_ph = $calib->ph_offset ?? 0;
        $offset_turb = $calib->turbidity_offset ?? 0;
        
        $final_ph = $sensor->ph + $offset_ph;
        $final_turbidity = $sensor->turbidity + $offset_turb;
        
        // Hitung status berdasarkan rule terbaru
        $phStatus = $this->getPhStatus($final_ph, $rule);
        $turbidityStatus = $this->getTurbidityStatusText($final_turbidity, $rule);
        
        // 🔥 UPDATE status di sensor_realtime
        DB::table('sensor_realtime')->update([
            'ph_status' => $phStatus,
            'turbidity_status' => $turbidityStatus,
            'updated_at' => now()
        ]);
        
        Log::info('Sensor status updated after rule change', [
            'ph' => $final_ph,
            'ph_status' => $phStatus,
            'turbidity' => $final_turbidity,
            'turbidity_status' => $turbidityStatus,
            'ph_offset' => $offset_ph,
            'turbidity_offset' => $offset_turb
        ]);
    }

    /**
     * Get pH status based on rules (untuk update sensor)
     */
    private function getPhStatus($ph, $rule = null)
    {
        if (!$rule) {
            $rule = RuleSensor::first();
        }
        
        if ($rule) {
            // Cek BAHAYA
            if ($ph <= $rule->ph_danger_low || $ph >= $rule->ph_danger_high) {
                return 'bahaya';
            }
            // Cek PERINGATAN
            if (($ph >= $rule->ph_min_warning && $ph <= $rule->ph_max_warning) || 
                ($ph >= $rule->ph_min_warning_high && $ph <= $rule->ph_max_warning_high)) {
                return 'peringatan';
            }
            // Cek BAIK
            if ($ph >= $rule->ph_min_good && $ph <= $rule->ph_max_good) {
                return 'baik';
            }
        }
        
        // Default rule
        if ($ph < 6.5 || $ph > 9.0) return 'bahaya';
        if ($ph < 7.0 || $ph > 8.5) return 'peringatan';
        return 'baik';
    }
}