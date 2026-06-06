<?php

namespace App\Http\Controllers;

use App\Models\PengaturanTambak;
use App\Models\RuleSensor;
use App\Models\JadwalPengingat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\TambakProfile; 
class PengaturanController extends Controller
{
    /**
     * Halaman utama pengaturan
     */
   public function index()
{
    $pengaturan = PengaturanTambak::first();
    $rule = RuleSensor::first();
    
    // ============================================
    // PAKAI DB FACADE UNTUK JADWAL
    // ============================================
    $jadwalList = DB::table('jadwal_pengingat')
        ->orderBy('jam', 'asc')
        ->get();
    
    // Format target_nomor
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
            
            // Clear cache agar perubahan langsung berlaku di SensorController
            Cache::forget('sensor_rule');
            
            Log::info('Rule updated and cache cleared', [
                'ph_min_good' => $rule->ph_min_good,
                'ph_max_good' => $rule->ph_max_good,
                'turbidity_max_good' => $rule->turbidity_max_good
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '✅ Rule sensor berhasil disimpan dan cache dibersihkan'
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
     * RESET RULE SENSOR KE DEFAULT NTU
     */
    public function resetRule()
    {
        try {
            $rule = RuleSensor::first();
            
            if (!$rule) {
                $rule = new RuleSensor();
            }
            
            // Reset ke default NTU
            $rule->ph_min_good = 7.5;
            $rule->ph_max_good = 8.5;
            $rule->ph_min_warning = 7.0;
            $rule->ph_max_warning = 9.0;
            $rule->ph_min_warning_high = 8.6;
            $rule->ph_max_warning_high = 8.9;
            $rule->ph_danger_low = 6.5;
            $rule->ph_danger_high = 9.5;
            
            // Turbidity NTU (0-1000)
            $rule->turbidity_min_good = 0;
            $rule->turbidity_max_good = 300;
            $rule->turbidity_min_warning = 301;
            $rule->turbidity_max_warning = 700;
            $rule->turbidity_danger_low = 0;
            $rule->turbidity_danger_high = 700;
            
            $rule->save();
            
            Cache::forget('sensor_rule');
            
            Log::info('Rule reset to default');
            
            return response()->json([
                'success' => true,
                'message' => '✅ Rule berhasil direset ke default NTU'
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
        // Hapus cache dulu
        Cache::forget('sensor_rule');
        
        $rule = RuleSensor::first();
        
        if (!$rule) {
            // Buat default NTU jika belum ada
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
/**
 * API: Get all jadwal pengingat
 */
/**
 * API: Get all jadwal pengingat
 */
/**
 * API: Get all jadwal pengingat
 */
public function getJadwalList()
{
    try {
        $jadwal = DB::table('jadwal_pengingat')
            ->orderBy('jam', 'asc')
            ->get();
        
        // Format target_nomor untuk ditampilkan
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
   /**
 * API: Store new jadwal pengingat
 */
/**
 * API: Store new jadwal pengingat
 */
/**
 * API: Store new jadwal pengingat
 */
/**
 * API: Store new jadwal pengingat
 */
/**
 * API: Store new jadwal pengingat
 */
public function storeJadwal(Request $request)
{
    try {
        Log::info('Store jadwal request:', $request->all());
        
        // Gunakan validasi sederhana tanpa regex dulu untuk testing
        $validated = $request->validate([
            'jam' => 'required|string',
            'pesan' => 'required|string|max:500',
            'target_nomor' => 'nullable|array'
        ]);
        
        // Validasi manual format jam (HH:MM)
        if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $validated['jam'])) {
            return response()->json([
                'success' => false,
                'message' => 'Format jam harus HH:MM (contoh: 14:30)'
            ], 422);
        }
        
        // Format nomor (pastikan diawali 62)
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
        
        // Gunakan Model
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
 /**
 * API: Delete jadwal pengingat
 */
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
     * API: Get realtime sensor untuk card monitoring
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
        
        // Hitung rekomendasi pakan
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
     * Hitung rekomendasi pakan berdasarkan kondisi air
     */
    private function hitungRekomendasiPakan($ph, $turbidity)
    {
        $targetPakan = 500; // gram per hari
        
        // Faktor koreksi berdasarkan kondisi
        $faktor = 1.0;
        
        if ($ph < 6.5 || $ph > 8.5 || $turbidity > 100) {
            $faktor = 0; // Bahaya → tidak usah kasih pakan
        } elseif ($ph < 7.0 || $ph > 8.0 || $turbidity > 50) {
            $faktor = 0.5; // Peringatan → kurangi 50%
        }
        
        return round(($targetPakan * $faktor) / 1000, 2); // dalam KG
    }
    
    /**
     * Get status text for pH
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
     * Get status text for turbidity
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
        // Contoh menggunakan OpenWeatherMap (daftar gratis di openweathermap.org)
        $apiKey = env('OPENWEATHER_API_KEY', '');
        $city = env('WEATHER_CITY', 'Jakarta');
        
        if (empty($apiKey)) {
            // Fallback ke data manual
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
        
        // Mapping cuaca dari API
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
        
        // Update ke database
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
        
        // Fallback
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
 * GET FEEDING RECOMMENDATION untuk card monitoring
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
        
        // Hitung rekomendasi pakan
        $biomassaKg = $profile->biomassa_kg ?? 0;
        $umurHari = $profile->umur_hari ?? 0;
        
        // Feeding rate berdasarkan umur (STANDAR YANG LEBIH AKURAT)
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
        
        // Faktor koreksi berdasarkan kondisi air
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
        
        // Keterangan
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
}