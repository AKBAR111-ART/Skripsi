<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonitoringData;
use App\Models\FeedingRecord;
use App\Models\SensorRealtime;
use App\Models\Sensor5MinAvg;
use App\Models\TambakProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    /**
     * Halaman utama monitoring
     */
    public function index()
    {
        // 🔥 PAKAI MODEL FeedingRecord
        $totalPakanGramHariIni = FeedingRecord::totalHariIni();
        $totalPakanKgHariIni = FeedingRecord::totalKgHariIni();
        
        // 🔥 JUMLAH PEMBERIAN HARI INI
        $jumlahPemberian = FeedingRecord::whereDate('created_at', Carbon::today())->count();
        
        // 🔥 ALIAS UNTUK VIEW
        $totalFeed = $totalPakanGramHariIni;
        $jumlahPemberianHariIni = $jumlahPemberian;
        
        // 🔥 TARGET PAKAN
        $targetPakanGram = 500;
        
        // 🔥 REALISASI PERSEN
        $realisasiPersen = $targetPakanGram > 0 
            ? round(($totalPakanGramHariIni / $targetPakanGram) * 100) 
            : 0;
        
        // 🔥 DATA SENSOR TERBARU
        $sensor = SensorRealtime::first();
        $currentPh = $sensor->ph ?? 7.0;
        $currentTurbidity = $sensor->turbidity ?? 30;
        
        // 🔥 STATUS UNTUK CARD
        $phStatus = $this->getPhStatus($currentPh);
        $turbidityStatus = $this->getTurbidityStatus($currentTurbidity);
        $currentStatus = $phStatus;
        
        // 🔥 REKOMENDASI PAKAN
        $rekomendasi = FeedingRecord::getFeedRecommendation($currentPh, $currentTurbidity, $targetPakanGram);
        
        // 🔥 BIOMASSA (kg)
        $profile = TambakProfile::first();
        $populasi = $profile->populasi ?? 0;
        $avgWeight = $profile->avg_weight ?? 0;
        $biomassaKg = ($populasi * $avgWeight) / 1000;
        
        // 🔥 FEED SCHEDULE
        $feedSchedule = FeedingRecord::getScheduleWithStatus();
        
        // 🔥 AMBIL DATE DARI REQUEST (default hari ini)
        $selectedDate = request()->get('date', Carbon::today()->format('Y-m-d'));
        
        // 🔥 DATA MONITORING (per 5 menit) BERDASARKAN TANGGAL
        $monitoringData = DB::table('sensor_5min_avg')
            ->whereDate('date', $selectedDate)
            ->orderBy('time_slot', 'asc')
            ->get();
        
        // 🔥 STATISTIK HARIAN
        $dailyStats = [
            'avg_ph' => round($monitoringData->avg('avg_ph'), 2),
            'avg_turbidity' => round($monitoringData->avg('avg_turbidity'), 2),
            'min_ph' => round($monitoringData->min('min_ph'), 2),
            'max_ph' => round($monitoringData->max('max_ph'), 2),
            'min_turbidity' => round($monitoringData->min('min_turbidity'), 2),
            'max_turbidity' => round($monitoringData->max('max_turbidity'), 2),
            'total_records' => $monitoringData->count(),
            'status' => $this->getDailyStatus($monitoringData)
        ];
        
        // 🔥 STATISTIK FEEDING
        $feedingStats = FeedingRecord::getFeedingStats();
        
        // 🔥 KIRIM KE VIEW
        return view('dashboard.monitoring', compact(
            'totalPakanGramHariIni',
            'totalPakanKgHariIni',
            'jumlahPemberian',
            'jumlahPemberianHariIni',
            'targetPakanGram',
            'realisasiPersen',
            'sensor',
            'currentPh',
            'currentTurbidity',
            'phStatus',
            'turbidityStatus',
            'currentStatus',
            'rekomendasi',
            'biomassaKg',
            'feedSchedule',
            'monitoringData',
            'feedingStats',
            'totalFeed',
            'selectedDate',
            'dailyStats'
        ));
    }

    /**
     * API: Get data monitoring per tanggal (untuk AJAX)
     */
   public function getMonitoringData(Request $request)
{
    $date = $request->get('date', Carbon::today()->format('Y-m-d'));
    
    // 🔥 PASTIKAN HANYA DATA DARI TANGGAL YANG DIPILIH
    $data = DB::table('sensor_5min_avg')
        ->whereDate('date', $date)  // ← SUDAH BENAR
        ->orderBy('time_slot', 'asc')
        ->get()
        ->map(function($item) {
            return [
                'waktu' => substr($item->time_slot, 0, 5),
                'ph' => $item->avg_ph,
                'kekeruhan' => $item->avg_turbidity,
                'min_ph' => $item->min_ph,
                'max_ph' => $item->max_ph,
                'status' => $item->status
            ];
        });
    
    // 🔥 HITUNG STATISTIK HANYA DARI DATA YANG DITAMPILKAN
    $stats = [
        'avg_ph' => round($data->avg('ph'), 2),
        'avg_turbidity' => round($data->avg('kekeruhan'), 2),
        'min_ph' => round($data->min('ph'), 2),
        'max_ph' => round($data->max('ph'), 2),
        'min_turbidity' => round($data->min('kekeruhan'), 2),
        'max_turbidity' => round($data->max('kekeruhan'), 2),
        'total_records' => $data->count(),
        'status' => $this->getDailyStatusFromCollection($data)
    ];
    
    return response()->json([
        'success' => true,
        'date' => $date,
        'data' => $data,
        'stats' => $stats
    ]);
}

    /**
     * Get daily status dari collection data
     */
    private function getDailyStatusFromCollection($data)
    {
        $kritisCount = $data->where('status', 'Kritis')->count();
        $warningCount = $data->where('status', 'Sedang')->count();
        
        if ($kritisCount > 0) return 'Kritis';
        if ($warningCount > 0) return 'Peringatan';
        return 'Normal';
    }

    /**
     * Get daily status dari collection model
     */
    private function getDailyStatus($data)
    {
        $kritisCount = $data->where('status', 'Kritis')->count();
        $warningCount = $data->where('status', 'Sedang')->count();
        
        if ($kritisCount > 0) return 'Kritis';
        if ($warningCount > 0) return 'Peringatan';
        return 'Normal';
    }
    
    /**
     * API: Get latest data for auto update
     */
    public function getLatestData()
    {
        $totalPakanGramHariIni = FeedingRecord::totalHariIni();
        $jumlahPemberian = FeedingRecord::whereDate('created_at', Carbon::today())->count();
        
        $sensor = SensorRealtime::first();
        
        $latestData = DB::table('sensor_5min_avg')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();
        
        return response()->json([
            'success' => true,
            'total_pakan_gram' => $totalPakanGramHariIni,
            'total_pakan_kg' => round($totalPakanGramHariIni / 1000, 2),
            'jumlah_pemberian' => $jumlahPemberian,
            'ph' => $sensor->ph ?? 0,
            'turbidity' => $sensor->turbidity ?? 0,
            'ph_status' => $sensor->ph_status ?? 'normal',
            'turbidity_status' => $sensor->turbidity_status ?? 'normal',
            'latest_data' => $latestData
        ]);
    }

    /**
     * Generate data monitoring berdasarkan umur budidaya
     */
    private function generateDataMonitoring($umurBudidaya)
    {
        // Pastikan umur minimal 1 hari
        if ($umurBudidaya < 1) {
            $umurBudidaya = 1;
        }
        
        // Hapus data lama untuk hari ini
        Sensor5MinAvg::whereDate('date', Carbon::today())->delete();
        
        // Parameter berdasarkan umur budidaya
        // Semakin tua umur, pH cenderung turun, kekeruhan naik
        $basePh = max(6.5, 7.5 - ($umurBudidaya / 100));
        $baseTurbidity = min(80, 25 + ($umurBudidaya / 2));
        
        // Generate data per 5 menit (06:00 - 22:00)
        for ($hour = 6; $hour <= 22; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 5) {
                $timeSlot = sprintf('%02d:%02d:00', $hour, $minute);
                
                // Fluktuasi per jam (siang lebih tinggi)
                $hourFactor = ($hour >= 10 && $hour <= 16) ? 0.1 : -0.05;
                $turbHourFactor = ($hour >= 10 && $hour <= 16) ? 5 : -2;
                
                $ph = $basePh + $hourFactor + (rand(-5, 5) / 100);
                $ph = round(max(6.5, min(8.5, $ph)), 2);
                
                $turbidity = $baseTurbidity + $turbHourFactor + rand(-5, 8);
                $turbidity = max(20, min(80, $turbidity));
                
                // Tentukan status
                $status = $this->getStatusFromValues($ph, $turbidity);
                
                $periodStart = Carbon::today()->setHour($hour)->setMinute($minute)->setSecond(0);
                $periodEnd = $periodStart->copy()->addMinutes(5);
                
                Sensor5MinAvg::create([
                    'avg_ph' => $ph,
                    'avg_turbidity' => $turbidity,
                    'min_ph' => round($ph - 0.1, 2),
                    'max_ph' => round($ph + 0.1, 2),
                    'min_turbidity' => max(20, $turbidity - 5),
                    'max_turbidity' => min(80, $turbidity + 5),
                    'sample_count' => rand(3, 10),
                    'status' => $status,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'date' => Carbon::today(),
                    'time_slot' => $timeSlot
                ]);
            }
        }
    }
    
    /**
     * Generate data pakan berdasarkan umur budidaya (tidak digunakan untuk manual)
     */
    private function generateDataPakan($umurBudidaya)
    {
        // Pastikan umur minimal 1 hari
        if ($umurBudidaya < 1) {
            $umurBudidaya = 1;
        }
        
        // Hapus data lama untuk hari ini
        FeedingRecord::whereDate('created_at', Carbon::today())->delete();
        
        // Hitung kebutuhan pakan berdasarkan umur (gram per ekor per hari)
        $pakanPerEkor = $this->getPakanByUmur($umurBudidaya);
        
        // Populasi (ambil dari profil atau default)
        $profile = TambakProfile::first();
        $populasi = $profile->populasi ?? 5000;
        
        // Total pakan per hari
        $totalPakan = $pakanPerEkor * $populasi;
        
        // Jadwal pakan (4x sehari)
        $jadwal = [
            ['pukul' => '06:00', 'persen' => 0.25],
            ['pukul' => '10:00', 'persen' => 0.25],
            ['pukul' => '14:00', 'persen' => 0.25],
            ['pukul' => '18:00', 'persen' => 0.25],
        ];
        
        foreach ($jadwal as $j) {
            $jumlah = round($totalPakan * $j['persen']);
            
            FeedingRecord::create([
                'pakan_gram' => $jumlah,
                'pakan_kg' => $jumlah / 1000,
                'jadwal' => $this->getJadwalName($j['pukul']),
                'waktu_pemberian' => Carbon::today()->setHour(substr($j['pukul'], 0, 2))->setMinute(0),
                'status' => 'Sudah',
                'keterangan' => 'Otomatis berdasarkan umur budidaya ' . $umurBudidaya . ' hari',
                'created_at' => Carbon::today()->setHour(substr($j['pukul'], 0, 2))->setMinute(0),
                'updated_at' => now()
            ]);
        }
    }
    
    /**
     * Get kebutuhan pakan per ekor berdasarkan umur (gram)
     */
    private function getPakanByUmur($umurHari)
    {
        // Tabel pakan berdasarkan minggu (umur 1-15 minggu)
        $tabelPakan = [
            1 => 0.5, 2 => 1.0, 3 => 2.0, 4 => 3.0, 5 => 4.5,
            6 => 6.0, 7 => 8.0, 8 => 10.0, 9 => 12.5, 10 => 15.0,
            11 => 18.0, 12 => 21.0, 13 => 25.0, 14 => 28.0, 15 => 31.0,
        ];
        
        // Jika umur 0 hari atau kurang, return nilai minimal
        if ($umurHari <= 0) {
            return 0.5;
        }
        
        // Konversi umur hari ke minggu (minimal 1)
        $minggu = max(1, (int)ceil($umurHari / 7));
        
        // Jika masih dalam tabel (1-15 minggu)
        if ($minggu <= 15) {
            return $tabelPakan[$minggu];
        }
        
        // Di atas 15 minggu, tambah 3.5 gram per minggu dari minggu ke-13
        return 25.0 + (($minggu - 13) * 3.5);
    }
    
    /**
     * Get berat rata-rata udang berdasarkan umur (gram)
     */
    private function getBeratByUmur($umurHari)
    {
        // Estimasi berat berdasarkan umur (gram)
        if ($umurHari <= 30) return 5;
        if ($umurHari <= 45) return 10;
        if ($umurHari <= 60) return 15;
        if ($umurHari <= 75) return 20;
        return 25;
    }
    
    private function getJadwalName($pukul)
    {
        $hour = (int)substr($pukul, 0, 2);
        if ($hour >= 5 && $hour < 9) return 'Pagi';
        if ($hour >= 9 && $hour < 13) return 'Siang';
        if ($hour >= 13 && $hour < 17) return 'Sore';
        return 'Malam';
    }
    
    private function getStatusFromValues($ph, $turbidity)
    {
        $phNormal = ($ph >= 7 && $ph <= 8);
        $turbNormal = ($turbidity < 30);
        
        if ($phNormal && $turbNormal) return 'Normal';
        if (!$phNormal && !$turbNormal) return 'Kritis';
        return 'Sedang';
    }
    
    /**
     * API: Get data realtime untuk gauge monitoring
     */
    public function getRealtime()
    {
        $sensor = SensorRealtime::first();
        
        return response()->json([
            'success' => true,
            'ph' => $sensor->ph ?? 7.0,
            'ph_status' => $sensor->ph_status ?? 'normal',
            'turbidity' => $sensor->turbidity ?? 30,
            'turbidity_status' => $sensor->turbidity_status ?? 'normal',
            'recorded_at' => $sensor ? $sensor->updated_at->format('H:i:s') : date('H:i:s')
        ]);
    }
    
    /**
     * API: Get statistik monitoring
     */
    public function getStats()
    {
        $sensor = SensorRealtime::latest()->first();
        $profile = TambakProfile::first();
        
        $currentPh = $sensor->ph ?? 7.8;
        $currentTurbidity = $sensor->turbidity ?? 30;
        
        $populasi = $profile->populasi ?? 0;
        $avgWeight = $profile->avg_weight ?? 0;
        $biomassaKg = ($populasi * $avgWeight) / 1000;
        $jumlahPemberian = FeedingRecord::whereDate('created_at', Carbon::today())->count();
        
        return response()->json([
            'success' => true,
            'ph_status' => $this->getPhStatus($currentPh),
            'turbidity_status' => $this->getTurbidityStatus($currentTurbidity),
            'biomassa' => round($biomassaKg, 2),
            'jumlah_pemberian' => $jumlahPemberian,
            'total_feed' => FeedingRecord::totalHariIni(),
            'water_quality' => $this->getWaterQualityText($currentPh, $currentTurbidity)
        ]);
    }
    
    /**
     * API: Get history monitoring untuk tabel
     */
    public function getMonitoringHistory(Request $request)
    {
        $range = $request->get('range', 'today');
        
        switch ($range) {
            case 'week':
                $start = Carbon::now()->subDays(7);
                break;
            case 'month':
                $start = Carbon::now()->subDays(30);
                break;
            default:
                $start = Carbon::today();
        }
        
        $data = MonitoringData::where('recorded_at', '>=', $start)
                    ->orderBy('recorded_at', 'desc')
                    ->get()
                    ->map(function($item) {
                        return [
                            'waktu' => $item->recorded_at->format('H:i'),
                            'ph' => $item->ph,
                            'kekeruhan' => $item->turbidity,
                            'status' => $item->status
                        ];
                    });
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    
    /**
     * API: Get chart history
     */
    public function getChartHistory(Request $request)
    {
        $range = $request->get('range', 'today');
        
        switch ($range) {
            case 'week':
                $start = Carbon::now()->subDays(7);
                $timeFormat = 'd M';
                break;
            case 'month':
                $start = Carbon::now()->subDays(30);
                $timeFormat = 'd M';
                break;
            default:
                $start = Carbon::today();
                $timeFormat = 'H:i';
        }
        
        $data = MonitoringData::where('recorded_at', '>=', $start)
                ->orderBy('recorded_at', 'asc')
                ->get()
                ->map(function($item) use ($timeFormat) {
                    return [
                        'ph' => $item->ph,
                        'turbidity' => $item->turbidity,
                        'time' => $item->recorded_at->format($timeFormat)
                    ];
                });
        
        return response()->json([
            'success' => true,
            'labels' => $data->pluck('time'),
            'ph_data' => $data->pluck('ph'),
            'turbidity_data' => $data->pluck('turbidity')
        ]);
    }
    
    /**
     * API: Get feeding data (REAL MANUAL)
     */
    public function getFeedingData()
    {
        $schedule = FeedingRecord::getScheduleWithStatus();
        $totalGram = FeedingRecord::totalHariIni();
        $jumlahPemberian = FeedingRecord::whereDate('created_at', Carbon::today())->count();
        
        return response()->json([
            'success' => true,
            'total_gram' => $totalGram,
            'jumlah_pemberian' => $jumlahPemberian,
            'schedule' => $schedule,
            'message' => 'Data pakan real manual hari ini'
        ]);
    }
    
    /**
     * API: Store monitoring data
     */
    public function storeMonitoring(Request $request)
    {
        $request->validate([
            'ph' => 'required|numeric|between:0,14',
            'turbidity' => 'required|numeric|between:0,100'
        ]);
        
        $status = MonitoringData::determineStatus($request->ph, $request->turbidity);
        
        $monitoring = MonitoringData::create([
            'ph' => $request->ph,
            'turbidity' => $request->turbidity,
            'status' => $status,
            'recorded_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Data monitoring berhasil disimpan',
            'data' => $monitoring
        ]);
    }
    
    /**
     * API: Store feeding record (MANUAL INPUT)
     */
    public function storeFeeding(Request $request)
    {
        $request->validate([
            'pakan_gram' => 'required|numeric|min:0',
            'pukul' => 'required|string'
        ]);
        
        $feeding = FeedingRecord::recordFeeding($request->pakan_gram, $request->pukul);
        
        return response()->json([
            'success' => true,
            'message' => 'Pakan berhasil dicatat',
            'data' => $feeding
        ]);
    }
    
    /**
     * API: Generate dummy data
     */
    public function generateDummyData()
    {
        // Hapus data hari ini
        MonitoringData::whereDate('recorded_at', Carbon::today())->delete();
        
        // Generate data setiap 2 jam
        $hours = [6, 8, 10, 12, 14, 16, 18, 20];
        $today = Carbon::today();
        
        foreach ($hours as $hour) {
            $ph = 7.5 + (rand(-20, 20) / 100);
            $ph = round(max(6.5, min(8.5, $ph)), 2);
            
            $turbidity = 28 + rand(-5, 15);
            $turbidity = max(20, min(80, $turbidity));
            
            $status = MonitoringData::determineStatus($ph, $turbidity);
            
            MonitoringData::create([
                'ph' => $ph,
                'turbidity' => $turbidity,
                'status' => $status,
                'recorded_at' => $today->copy()->setHour($hour)->setMinute(rand(0, 59))
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Data dummy berhasil digenerate'
        ]);
    }
    
    /**
     * API: Get data untuk grafik (dari tabel rata-rata)
     */
    public function getChartDataFromAvg(Request $request)
    {
        $range = $request->get('range', 'today');
        
        switch ($range) {
            case 'week':
                $startDate = Carbon::now()->subDays(7);
                $data = Sensor5MinAvg::whereBetween('date', [$startDate, Carbon::today()])
                            ->orderBy('date', 'asc')
                            ->orderBy('time_slot', 'asc')
                            ->get()
                            ->groupBy('date');
                
                $result = [];
                foreach ($data as $date => $items) {
                    $result[] = [
                        'date' => $date,
                        'avg_ph' => round($items->avg('avg_ph'), 2),
                        'avg_turbidity' => round($items->avg('avg_turbidity'), 1),
                        'min_ph' => round($items->min('min_ph'), 2),
                        'max_ph' => round($items->max('max_ph'), 2)
                    ];
                }
                break;
                
            case 'month':
                $startDate = Carbon::now()->subDays(30);
                $data = Sensor5MinAvg::whereBetween('date', [$startDate, Carbon::today()])
                            ->orderBy('date', 'asc')
                            ->get()
                            ->groupBy('date');
                
                $result = [];
                foreach ($data as $date => $items) {
                    $result[] = [
                        'date' => $date,
                        'avg_ph' => round($items->avg('avg_ph'), 2),
                        'avg_turbidity' => round($items->avg('avg_turbidity'), 1)
                    ];
                }
                break;
                
            default:
                $result = Sensor5MinAvg::whereDate('date', Carbon::today())
                            ->orderBy('time_slot', 'asc')
                            ->get()
                            ->map(function($item) {
                                return [
                                    'time' => $item->time_slot,
                                    'ph' => $item->avg_ph,
                                    'turbidity' => $item->avg_turbidity,
                                    'min_ph' => $item->min_ph,
                                    'max_ph' => $item->max_ph
                                ];
                            });
                break;
        }
        
        return response()->json([
            'success' => true,
            'data' => $result,
            'range' => $range
        ]);
    }
    
    // ========== HELPER FUNCTIONS ==========
    
    private function getPhStatus($ph)
    {
        if ($ph >= 7 && $ph <= 8) return 'Normal';
        if ($ph > 8) return 'Tinggi';
        return 'Rendah';
    }
    
    private function getTurbidityStatus($turbidity)
    {
        if ($turbidity < 30) return 'Normal';
        if ($turbidity < 60) return 'Sedang';
        return 'Tinggi';
    }
    
    private function getWaterQualityText($ph, $turbidity)
    {
        if ($ph >= 7 && $ph <= 8 && $turbidity < 30) return 'Terpantau - Stabil';
        if ($ph < 7) return 'Terpantau - pH Rendah';
        if ($ph > 8) return 'Terpantau - pH Tinggi';
        if ($turbidity >= 60) return 'Terpantau - Air Sangat Keruh';
        if ($turbidity >= 30) return 'Terpantau - Air Keruh';
        return 'Terpantau';
    }
}