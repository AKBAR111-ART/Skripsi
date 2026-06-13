<?php
namespace App\Http\Controllers;

use App\Models\TambakProfile; 
use App\Models\Sensor;
use App\Models\SensorRealtime;
use App\Models\RuleSensor;
use App\Models\SensorCalibration;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SensorController extends Controller
{
    private $BATCH_SIZE = 10;
    
    /**
     * NOTIFIKASI WHATSAPP (FONNTE)
     */
    private function sendWhatsAppNotification($message, $target = null)
    {
        try {
            $targetNumber = $target ?? env('FONNTE_TARGET', '62895379348181');
            $apiKey = env('FONNTE_TOKEN');
            
            if (!$apiKey) {
                Log::warning('FONNTE_TOKEN not set in .env');
                return false;
            }
            
            $response = Http::withHeaders([
                'Authorization' => $apiKey
            ])->post('https://api.fonnte.com/send', [
                'target' => $targetNumber,
                'message' => $message
            ]);
            
            if ($response->successful()) {
                Log::info('WhatsApp notification sent', ['message' => $message]);
                return true;
            }
            
            Log::error('WhatsApp notification failed', ['response' => $response->body()]);
            return false;
            
        } catch (\Exception $e) {
            Log::error('WhatsApp error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * CEK DAN KIRIM NOTIFIKASI BERDASARKAN KONDISI
     */
    private function cekDanKirimNotifikasi($ph, $turbidity, $statusPh, $statusTurbidity, $cuaca, $intensitasHujan, $rekomendasi)
    {
        $notifikasiDikirim = false;
        $lastNotifKey = 'last_notification_sent';
        $lastNotif = Cache::get($lastNotifKey);
        $now = now();
        
        if ($lastNotif && $lastNotif->diffInHours($now) < 1) {
            return false;
        }
        
        $message = "";
        $isBahaya = ($statusPh === 'bahaya' || $statusTurbidity === 'bahaya');
        $isPeringatan = ($statusPh === 'peringatan' || $statusTurbidity === 'peringatan');
        $isHujan = ($cuaca === 'Hujan' && $intensitasHujan > 0);
        
        if ($isBahaya) {
            $message = "🔴 *PERINGATAN BAHAYA!* 🔴\n\n";
            $message .= "📍 *Kondisi Tambak Kritis!*\n";
            $message .= "──────────────────\n";
            $message .= "🦐 *pH Air:* {$ph} (BAHAYA)\n";
            $message .= "💧 *Kekeruhan:* {$turbidity} NTU (BAHAYA)\n";
            if ($isHujan) {
                $message .= "🌧️ *Cuaca:* {$cuaca} ({$intensitasHujan} mm)\n";
            }
            $message .= "──────────────────\n";
            $message .= "⚠️ *TINDAKAN YANG HARUS DILAKUKAN:*\n";
            $message .= "1. Hentikan pemberian pakan\n";
            $message .= "2. Cek kualitas air secara manual\n";
            $message .= "3. Lakukan pergantian air 30-50%\n";
            $message .= "4. Hubungi teknisi tambak\n\n";
            $message .= "⏰ *Waktu:* " . $now->format('d/m/Y H:i:s');
            
            $this->sendWhatsAppNotification($message);
            Cache::put($lastNotifKey, $now, now()->addHours(1));
            return true;
        }
        
        if ($isHujan && $isPeringatan) {
            $message = "⚠️ *KOMBINASI BERBAHAYA!* ⚠️\n\n";
            $message .= "📍 *Hujan + Kualitas Air Tidak Stabil*\n";
            $message .= "──────────────────\n";
            $message .= "🌧️ *Cuaca:* Hujan ({$intensitasHujan} mm)\n";
            $message .= "🦐 *pH Air:* {$ph} (PERINGATAN)\n";
            $message .= "💧 *Kekeruhan:* {$turbidity} NTU (PERINGATAN)\n";
            $message .= "──────────────────\n";
            $message .= "⚠️ *TINDAKAN YANG HARUS DILAKUKAN:*\n";
            $message .= "1. Kurangi pakan 50-70%\n";
            $message .= "2. Pantau kondisi air setiap 2 jam\n";
            $message .= "3. Siapkan aerasi tambahan\n";
            $message .= "4. Hindari pergantian air saat hujan\n\n";
            $message .= "🍽️ *Rekomendasi Pakan:* {$rekomendasi} kg/hari\n\n";
            $message .= "⏰ *Waktu:* " . $now->format('d/m/Y H:i:s');
            
            $this->sendWhatsAppNotification($message);
            Cache::put($lastNotifKey, $now, now()->addHours(1));
            return true;
        }
        
        if ($isHujan && $intensitasHujan > 5) {
            $message = "🌧️ *PERINGATAN HUJAN LEBAT!* 🌧️\n\n";
            $message .= "📍 *Cuaca Buruk Terdeteksi*\n";
            $message .= "──────────────────\n";
            $message .= "🌧️ *Intensitas:* {$intensitasHujan} mm\n";
            $message .= "🦐 *Kondisi Air:* pH {$ph} | NTU {$turbidity}\n";
            $message .= "──────────────────\n";
            $message .= "⚠️ *TINDAKAN YANG HARUS DILAKUKAN:*\n";
            $message .= "1. Kurangi pakan 50%\n";
            $message .= "2. Periksa saluran air\n";
            $message .= "3. Pastikan aerasi berjalan normal\n\n";
            $message .= "🍽️ *Rekomendasi Pakan:* {$rekomendasi} kg/hari\n\n";
            $message .= "⏰ *Waktu:* " . $now->format('d/m/Y H:i:s');
            
            $this->sendWhatsAppNotification($message);
            Cache::put($lastNotifKey, $now, now()->addHours(1));
            return true;
        }
        
        if ($isPeringatan) {
            $message = "⚠️ *PERINGATAN KUALITAS AIR!* ⚠️\n\n";
            $message .= "📍 *Kualitas Air Tidak Stabil*\n";
            $message .= "──────────────────\n";
            $message .= "🦐 *pH Air:* {$ph} (PERINGATAN)\n";
            $message .= "💧 *Kekeruhan:* {$turbidity} NTU (PERINGATAN)\n";
            $message .= "──────────────────\n";
            $message .= "⚠️ *TINDAKAN YANG HARUS DILAKUKAN:*\n";
            $message .= "1. Kurangi pakan 50%\n";
            $message .= "2. Cek sumber air\n";
            $message .= "3. Tambah probiotik jika perlu\n\n";
            $message .= "🍽️ *Rekomendasi Pakan:* {$rekomendasi} kg/hari\n\n";
            $message .= "⏰ *Waktu:* " . $now->format('d/m/Y H:i:s');
            
            $this->sendWhatsAppNotification($message);
            Cache::put($lastNotifKey, $now, now()->addHours(1));
            return true;
        }
        
        return false;
    }
    
    /**
     * HITUNG REKOMENDASI PAKAN TERINTEGRASI
     */
    private function hitungRekomendasiPakanTerintegrasi($ph, $turbidity, $profile, $weatherData)
    {
        $biomassaKg = $profile->biomassa_kg;
        $umurHari = $profile->umur_hari;
        $populasi = $profile->populasi;
        $avgWeight = $profile->avg_weight;
        
        if ($umurHari <= 7) {
            $feedingRate = 0.10;
            $keteranganUmur = 'Awal tebar (0-7 hari) - Maks 10% biomassa';
        } elseif ($umurHari <= 14) {
            $feedingRate = 0.08;
            $keteranganUmur = 'Pertumbuhan awal (8-14 hari) - Maks 8% biomassa';
        } elseif ($umurHari <= 21) {
            $feedingRate = 0.07;
            $keteranganUmur = 'Pertumbuhan aktif (15-21 hari) - Maks 7% biomassa';
        } elseif ($umurHari <= 28) {
            $feedingRate = 0.06;
            $keteranganUmur = 'Pertumbuhan lanjut (22-28 hari) - Maks 6% biomassa';
        } elseif ($umurHari <= 42) {
            $feedingRate = 0.05;
            $keteranganUmur = 'Pertumbuhan maksimal (29-42 hari) - Maks 5% biomassa';
        } elseif ($umurHari <= 56) {
            $feedingRate = 0.04;
            $keteranganUmur = 'Menjelang panen (43-56 hari) - Maks 4% biomassa';
        } else {
            $feedingRate = 0.03;
            $keteranganUmur = 'Panen (>56 hari) - Maks 3% biomassa';
        }
        
        $rule = $this->getRuleFromCache();
        $statusPh = $this->getPhStatus($ph, $rule);
        $statusTurbidity = $this->getTurbidityStatus($turbidity, $rule);
        
        if ($statusPh === 'bahaya' || $statusTurbidity === 'bahaya') {
            $faktorAir = 0;
            $statusAir = 'bahaya';
        } elseif ($statusPh === 'peringatan' || $statusTurbidity === 'peringatan') {
            $faktorAir = 0.5;
            $statusAir = 'peringatan';
        } else {
            $faktorAir = 1.0;
            $statusAir = 'aman';
        }
        
        $cuaca = $weatherData['cuaca'] ?? 'Cerah';
        $intensitasHujan = $weatherData['intensitas_hujan'] ?? 0;
        $suhuLingkungan = $weatherData['suhu'] ?? 28;
        
        if ($cuaca === 'Hujan') {
            if ($intensitasHujan > 10) {
                $faktorCuaca = 0.3;
                $statusCuaca = 'hujan_lebat';
                $keteranganCuaca = 'Hujan lebat >10mm - kurangi pakan 70%';
            } elseif ($intensitasHujan > 2) {
                $faktorCuaca = 0.6;
                $statusCuaca = 'hujan_sedang';
                $keteranganCuaca = 'Hujan sedang - kurangi pakan 40%';
            } else {
                $faktorCuaca = 0.8;
                $statusCuaca = 'hujan_ringan';
                $keteranganCuaca = 'Hujan ringan - kurangi pakan 20%';
            }
        } else {
            $faktorCuaca = 1.0;
            $statusCuaca = 'cerah';
            $keteranganCuaca = 'Cuaca cerah/berawan - pakan normal';
        }
        
        if ($faktorAir == 0) {
            $faktorCuaca = 1.0;
            $statusCuaca = 'diabaikan';
            $keteranganCuaca = 'Faktor cuaca diabaikan karena kondisi air bahaya';
        }
        
        $pakanDasarKg = round($biomassaKg * $feedingRate, 2);
        $pakanSetelahAir = round($pakanDasarKg * $faktorAir, 2);
        $pakanRekomendasiKg = round($pakanSetelahAir * $faktorCuaca, 2);
        
        $maxPakan = round($biomassaKg * 0.08, 2);
        if ($pakanRekomendasiKg > $maxPakan && $umurHari > 14) {
            $pakanRekomendasiKg = $maxPakan;
            Log::info('Pakan direduksi ke batas maksimal 8% biomassa', [
                'original' => $pakanRekomendasiKg,
                'max' => $maxPakan
            ]);
        }
        
        if ($pakanRekomendasiKg < 0.5 && $statusAir !== 'bahaya' && $faktorAir > 0) {
            $pakanRekomendasiKg = 0.5;
        }
        
        $umurMinggu = ceil($umurHari / 7);
        $pakanPerEkor = $this->getPakanPerEkor($umurMinggu);
        $pakanDasarPopulasiGram = $populasi * $pakanPerEkor;
        $pakanDasarPopulasiGram = max(50, min($pakanDasarPopulasiGram, 10000));
        $pakanDasarPopulasiKg = round($pakanDasarPopulasiGram / 1000, 2);
        $pakanRekomendasiPopulasiKg = round($pakanDasarPopulasiKg * $faktorAir * $faktorCuaca, 2);
        
        Log::info('Rekomendasi Pakan Terintegrasi', [
            'biomassa_kg' => $biomassaKg,
            'umur_hari' => $umurHari,
            'feeding_rate' => $feedingRate,
            'pakan_dasar_kg' => $pakanDasarKg,
            'status_ph' => $statusPh,
            'status_turbidity' => $statusTurbidity,
            'faktor_air' => $faktorAir,
            'cuaca' => $cuaca,
            'intensitas_hujan' => $intensitasHujan,
            'faktor_cuaca' => $faktorCuaca,
            'rekomendasi_kg' => $pakanRekomendasiKg,
            'max_pakan' => $maxPakan
        ]);
        
        return [
            'rekomendasi_kg' => $pakanRekomendasiKg,
            'rekomendasi_gram' => round($pakanRekomendasiKg * 1000),
            'pakan_dasar_kg' => $pakanDasarKg,
            'pakan_setelah_air_kg' => $pakanSetelahAir,
            'faktor_air' => $faktorAir,
            'faktor_cuaca' => $faktorCuaca,
            'feeding_rate' => $feedingRate,
            'keterangan_umur' => $keteranganUmur,
            'keterangan_cuaca' => $keteranganCuaca,
            'status_air' => $statusAir,
            'status_cuaca' => $statusCuaca,
            'status_ph' => $statusPh,
            'status_turbidity' => $statusTurbidity,
            'biomassa_kg' => $biomassaKg,
            'umur_hari' => $umurHari,
            'umur_minggu' => $umurMinggu,
            'populasi' => $populasi,
            'avg_weight' => $avgWeight,
            'ph' => $ph,
            'turbidity' => $turbidity,
            'cuaca' => $cuaca,
            'intensitas_hujan' => $intensitasHujan,
            'suhu_lingkungan' => $suhuLingkungan,
            'metode_populasi' => [
                'pakan_per_ekor_gram' => $pakanPerEkor,
                'pakan_dasar_kg' => $pakanDasarPopulasiKg,
                'rekomendasi_kg' => $pakanRekomendasiPopulasiKg
            ]
        ];
    }
    
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
                'api_key' => 'nullable|string'
            ]);
            
            $apiKey = $request->get('api_key') ?? $request->header('X-API-Key');
            if ($apiKey !== env('ESP32_API_KEY')) {
                Log::warning('Invalid API Key from ESP32', ['received' => $apiKey]);
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized - Invalid API Key'
                ], 401);
            }
            
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
            
            $profile = TambakProfile::first();
            
            $weatherService = new WeatherService();
            $weatherData = $weatherService->getWeather();
            
            $cuaca = $weatherData['cuaca'];
            $intensitasHujan = $weatherData['intensitas_hujan'];
            $suhuLingkungan = $weatherData['suhu'];
            
            $rekomendasiData = [];
            if ($profile && $ph && $turbidity) {
                $rekomendasiData = $this->hitungRekomendasiPakanTerintegrasi($ph, $turbidity, $profile, $weatherData);
            }
            
            if ($profile) {
                $profile->update([
                    'cuaca' => $cuaca,
                    'intensitas_hujan' => $intensitasHujan
                ]);
                Log::info('Weather data saved to tambak_profile', [
                    'cuaca' => $cuaca,
                    'intensitas_hujan' => $intensitasHujan
                ]);
            }
            
            $this->cekDanKirimNotifikasi(
                $ph, 
                $turbidity, 
                $statusPh, 
                $statusTurbidity, 
                $cuaca, 
                $intensitasHujan,
                $rekomendasiData['rekomendasi_kg'] ?? 0
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Data sensor diterima',
                'data' => [
                    'ph' => $ph,
                    'ph_status' => $statusPh,
                    'turbidity' => $turbidity,
                    'turbidity_status' => $statusTurbidity,
                    'populasi' => $profile->populasi ?? null,
                    'avg_weight' => $profile->avg_weight ?? null,
                    'biomassa_kg' => $profile->biomassa_kg ?? null,
                    'umur_hari' => $profile->umur_hari ?? null,
                    'rekomendasi_pakan_kg' => $rekomendasiData['rekomendasi_kg'] ?? 0,
                    'rekomendasi_pakan_gram' => $rekomendasiData['rekomendasi_gram'] ?? 0,
                    'pakan_dasar_kg' => $rekomendasiData['pakan_dasar_kg'] ?? 0,
                    'faktor_air' => $rekomendasiData['faktor_air'] ?? 1,
                    'faktor_cuaca' => $rekomendasiData['faktor_cuaca'] ?? 1,
                    'status_air' => $rekomendasiData['status_air'] ?? 'aman',
                    'status_cuaca' => $rekomendasiData['status_cuaca'] ?? 'cerah',
                    'keterangan' => $this->getKeteranganRekomendasi($rekomendasiData),
                    'cuaca' => $cuaca,
                    'intensitas_hujan' => $intensitasHujan,
                    'suhu_lingkungan' => $suhuLingkungan,
                    'last_update' => now()->toDateTimeString()
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
     * GET FEEDING RECOMMENDATION
     */
    public function getFeedingRecommendation()
    {
        try {
            $sensor = DB::table('sensor_realtime')->first();
            $profile = TambakProfile::first();
            
            Log::info('Feeding recommendation requested', [
                'sensor_ph' => $sensor->ph ?? 'null',
                'sensor_turbidity' => $sensor->turbidity ?? 'null'
            ]);
            
            if (!$profile || !$profile->populasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data populasi belum diisi di halaman Profile'
                ], 400);
            }
            
            $weatherService = new WeatherService();
            $weatherData = $weatherService->getWeather();
            
            $rekomendasi = $this->hitungRekomendasiPakanTerintegrasi(
                $sensor->ph ?? 7, 
                $sensor->turbidity ?? 30, 
                $profile, 
                $weatherData
            );
            
            return response()->json([
                'success' => true,
                'data' => $rekomendasi
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get feeding recommendation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rekomendasi_kg' => 0.5,
                    'rekomendasi_gram' => 500,
                    'keterangan' => '✅ Data default (gunakan data profil yang valid)'
                ]
            ]);
        }
    }
    
    private function getKeteranganRekomendasi($rekomendasiData)
    {
        $statusAir = $rekomendasiData['status_air'] ?? 'aman';
        $statusCuaca = $rekomendasiData['status_cuaca'] ?? 'cerah';
        
        if ($statusAir === 'bahaya') {
            return '🔴 KONDISI AIR BAHAYA! Hentikan pemberian pakan sementara. Periksa kolam segera!';
        }
        
        if ($statusAir === 'peringatan' && $statusCuaca === 'hujan_lebat') {
            return '⚠️ PERINGATAN! Hujan lebat + kualitas air kurang baik. Kurangi pakan 70%. Pantau ketat!';
        }
        
        if ($statusAir === 'peringatan' && $statusCuaca === 'hujan_sedang') {
            return '⚠️ PERINGATAN! Hujan sedang + kualitas air kurang baik. Kurangi pakan 50%.';
        }
        
        if ($statusAir === 'peringatan' && $statusCuaca === 'hujan_ringan') {
            return '⚠️ PERINGATAN! Hujan gerimis + kualitas air kurang baik. Kurangi pakan 40%.';
        }
        
        if ($statusAir === 'peringatan') {
            return '⚠️ Kualitas air kurang baik. Kurangi pakan 50%.';
        }
        
        if ($statusCuaca === 'hujan_lebat') {
            return '🌧️ HUJAN LEBAT! Kurangi pakan 70%. Udang stres, kurangi frekuensi pemberian.';
        }
        
        if ($statusCuaca === 'hujan_sedang') {
            return '🌧️ HUJAN SEDANG! Kurangi pakan 40%. Pantau kualitas air.';
        }
        
        if ($statusCuaca === 'hujan_ringan') {
            return '🌦️ HUJAN RINGAN! Kurangi pakan 20%.';
        }
        
        return '✅ Kualitas air optimal. Berikan pakan sesuai jadwal.';
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
    
    private function getPhStatus($ph, $rule = null)
    {
        if (!$rule) return 'aman';
        
        if ($ph < $rule->ph_danger_low || $ph > $rule->ph_danger_high) {
            Log::info("pH {$ph} is BAHAYA");
            return 'bahaya';
        }
        
        if ($ph < $rule->ph_min_good || $ph > $rule->ph_max_good) {
            Log::info("pH {$ph} is PERINGATAN");
            return 'peringatan';
        }
        
        Log::info("pH {$ph} is AMAN");
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
            Log::info("Turbidity {$turbidity} is BAHAYA");
            return 'bahaya';
        }
        
        if ($turbidity > $rule->turbidity_max_good) {
            Log::info("Turbidity {$turbidity} is PERINGATAN");
            return 'peringatan';
        }
        
        return 'baik';
    }
    
    private function getRuleFromCache()
    {
        Cache::forget('sensor_rule');
        
        return Cache::remember('sensor_rule', 600, function () {
            $rule = RuleSensor::first();
            Log::info('Rule loaded from database');
            return $rule;
        });
    }
    
    /**
     * REALTIME - DENGAN KALIBRASI
     */
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
        
        $calibratedPh = SensorCalibration::getCalibratedValue($sensor->ph ?? 7, 'ph');
        $calibratedTurbidity = SensorCalibration::getCalibratedValue($sensor->turbidity ?? 30, 'turbidity');
        
        $phStatus = $this->getPhStatus($calibratedPh, $rule);
        $turbidityStatus = $this->getTurbidityStatus($calibratedTurbidity, $rule);
        
        $calibration = SensorCalibration::getCalibration();
        
        return response()->json([
            'success' => true,
            'ph' => (float)$calibratedPh,
            'ph_raw' => (float)$sensor->ph,
            'ph_status' => $phStatus,
            'ph_offset' => (float)$calibration->ph_offset,
            'turbidity' => (float)$calibratedTurbidity,
            'turbidity_raw' => (float)$sensor->turbidity,
            'turbidity_status' => $turbidityStatus,
            'turbidity_offset' => (float)$calibration->turbidity_offset,
            'is_calibrated' => $calibration->is_calibrated,
            'noise_level' => $calibration->noise_level,
            'last_calibration' => $calibration->last_calibration,
            'last_update' => $sensor->updated_at ?? $sensor->created_at
        ]);
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
    
    private function addToBuffer($data)
    {
        $buffer = Cache::get('sensor_batch_buffer', []);
        $buffer[] = $data;
        
        $lastFlush = Cache::get('sensor_last_flush', now());
        
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
        $exists = DB::table('sensor_realtime')->exists();
        
        if ($exists) {
            DB::table('sensor_realtime')->update([
                'ph' => $ph,
                'ph_status' => $statusPh,
                'turbidity' => $turbidity,
                'turbidity_status' => $statusTurbidity,
                'updated_at' => now()
            ]);
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
        }
        
        Cache::put('sensor_realtime_display', [
            'success' => true,
            'ph' => (float)$ph,
            'ph_status' => $statusPh,
            'turbidity' => (float)$turbidity,
            'turbidity_status' => $statusTurbidity,
            'last_update' => now()
        ], now()->addSeconds(5));
    }
    
    public function sendFeedCommand(Request $request)
    {
        try {
            Log::info('Send feed command received:', $request->all());
            
            $pakanGram = $request->pakan_gram ?? $request->pakan ?? $request->amount_gram ?? null;
            
            if (!$pakanGram) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pakan tidak ditemukan.'
                ], 400);
            }
            
            $pakanGram = (int)$pakanGram;
            $pakanKg = round($pakanGram / 1000, 2);
            
            if ($pakanGram < 1 || $pakanGram > 10000) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pakan harus antara 1-10000 gram'
                ], 422);
            }
            
            $jadwal = $request->jadwal ?? $this->getCurrentSchedule();
            $sumber = $request->sumber ?? 'manual';
            
            DB::table('feeding_records')->insert([
                'pakan_kg' => $pakanKg,
                'target_gram' => $pakanGram,
                'jadwal' => $jadwal,
                'sumber' => $sumber,
                'status' => 'success',
                'tanggal' => now()->toDateString(),
                'waktu_pemberian' => now()->toTimeString(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            Log::info('Feed record saved', [
                'pakan_gram' => $pakanGram,
                'pakan_kg' => $pakanKg,
                'jadwal' => $jadwal
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
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
            
            $feedData = DB::table('feeding_records')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(target_gram) as total_gram'))
                ->whereBetween('created_at', [$startDate, $endDate])
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
    
    // ==================== API PRODUCTION VARIABLES ====================
    
    public function getProductionVariables()
    {
        try {
            $profile = TambakProfile::first();
            $sensor = DB::table('sensor_realtime')->first();
            $rule = $this->getRuleFromCache();
            $pengaturan = DB::table('pengaturan_tambak')->first();
            
            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data profil tambak tidak ditemukan'
                ], 404);
            }
            
            $weatherService = new WeatherService();
            $weatherData = $weatherService->getWeather();
            
            $rekomendasi = $this->hitungRekomendasiPakanTerintegrasi(
                $sensor->ph ?? 7, 
                $sensor->turbidity ?? 30, 
                $profile, 
                $weatherData
            );
            
            $totalPakanHarianGram = DB::table('feeding_records')
                ->whereDate('created_at', Carbon::today())
                ->sum('target_gram');
            
            $fcr = 0;
            $biomassaKg = $profile->biomassa_kg;
            if ($biomassaKg > 0 && $totalPakanHarianGram > 0) {
                $fcr = round(($totalPakanHarianGram / 1000) / $biomassaKg, 2);
            }
            
            $jadwalPakan = [];
            if ($pengaturan && $pengaturan->waktu) {
                $jadwalPakan = json_decode($pengaturan->waktu, true);
                if (!is_array($jadwalPakan)) {
                    $jadwalPakan = [];
                }
            }
            
            $calibratedPh = SensorCalibration::getCalibratedValue($sensor->ph ?? 7, 'ph');
            $calibratedTurbidity = SensorCalibration::getCalibratedValue($sensor->turbidity ?? 30, 'turbidity');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'populasi_awal' => (int)($profile->populasi_awal ?? $profile->populasi ?? 0),
                    'populasi_saat_ini' => (int)($profile->populasi ?? 0),
                    'survival_rate' => $profile->survival_rate,
                    'avg_weight' => (float)($profile->avg_weight ?? 0),
                    'biomassa_kg' => $profile->biomassa_kg,
                    'umur_hari' => $profile->umur_hari,
                    'umur_minggu' => $profile->umur_minggu,
                    'target_panen_kg' => (float)($profile->target_panen_kg ?? 0),
                    'target_size_gram' => (float)($profile->target_size_gram ?? 0),
                    'daily_growth_rate' => $profile->daily_growth_rate,
                    'fcr' => $fcr,
                    'prediksi_panen_hari' => $profile->prediksi_panen_hari,
                    'ph' => (float)$calibratedPh,
                    'ph_status' => $this->getPhStatus($calibratedPh, $rule),
                    'turbidity' => (float)$calibratedTurbidity,
                    'turbidity_status' => $this->getTurbidityStatus($calibratedTurbidity, $rule),
                    'rekomendasi_pakan_kg' => $rekomendasi['rekomendasi_kg'] ?? 0,
                    'rekomendasi_pakan_gram' => $rekomendasi['rekomendasi_gram'] ?? 0,
                    'pakan_dasar_kg' => $rekomendasi['pakan_dasar_kg'] ?? 0,
                    'faktor_air' => $rekomendasi['faktor_air'] ?? 1,
                    'faktor_cuaca' => $rekomendasi['faktor_cuaca'] ?? 1,
                    'status_air' => $rekomendasi['status_air'] ?? 'aman',
                    'status_cuaca' => $rekomendasi['status_cuaca'] ?? 'cerah',
                    'total_pakan_harian_gram' => (int)$totalPakanHarianGram,
                    'total_pakan_harian_kg' => round($totalPakanHarianGram / 1000, 2),
                    'frekuensi_pemberian' => count($jadwalPakan),
                    'jadwal_pakan' => $jadwalPakan,
                    'cuaca' => $weatherData['cuaca'],
                    'intensitas_hujan' => $weatherData['intensitas_hujan'],
                    'suhu_lingkungan' => $weatherData['suhu'],
                    'kelembaban' => $weatherData['kelembaban'],
                    'last_update' => now()->toDateTimeString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get production variables error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * KIRIM STATUS KONDISI KE ESP32
     * GET /api/sensor/status-for-esp
     */
    public function getStatusForEsp(Request $request)
    {
        try {
            $apiKey = $request->get('api_key') ?? $request->header('X-API-Key');
            if ($apiKey !== env('ESP32_API_KEY')) {
                Log::warning('Invalid API Key from ESP32 for status request', ['received' => $apiKey]);
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized - Invalid API Key'
                ], 401);
            }
            
            $sensorRealtime = DB::table('sensor_realtime')->first();
            $rule = $this->getRuleFromCache();
            
            $ph = $sensorRealtime->ph ?? 7.0;
            $turbidity = $sensorRealtime->turbidity ?? 30;
            
            $calibratedPh = SensorCalibration::getCalibratedValue($ph, 'ph');
            $calibratedTurbidity = SensorCalibration::getCalibratedValue($turbidity, 'turbidity');
            
            $phStatus = $this->getPhStatus($calibratedPh, $rule);
            $turbidityStatus = $this->getTurbidityStatus($calibratedTurbidity, $rule);
            
            $overallStatus = $this->determineOverallStatus($phStatus, $turbidityStatus);
            
            $statusMapping = [
                'aman' => 'AMAN',
                'baik' => 'AMAN',
                'peringatan' => 'PERINGATAN',
                'bahaya' => 'BAHAYA'
            ];
            
            $espStatus = $statusMapping[$overallStatus] ?? 'AMAN';
            
            $weatherService = new WeatherService();
            $weatherData = $weatherService->getWeather();
            
            $profile = TambakProfile::first();
            $rekomendasiData = [];
            if ($profile) {
                $rekomendasiData = $this->hitungRekomendasiPakanTerintegrasi(
                    $calibratedPh, 
                    $calibratedTurbidity, 
                    $profile, 
                    $weatherData
                );
            }
            
            return response()->json([
                'success' => true,
                'timestamp' => now()->toIso8601String(),
                'status' => $espStatus,
                'overall_status' => $espStatus,
                'ph' => (float)$calibratedPh,
                'ph_status' => strtoupper($phStatus),
                'turbidity' => (float)$calibratedTurbidity,
                'turbidity_status' => strtoupper($turbidityStatus),
                'additional_info' => [
                    'message' => $this->getEspStatusMessage($espStatus),
                    'rekomendasi_pakan_kg' => $rekomendasiData['rekomendasi_kg'] ?? 0,
                    'cuaca' => $weatherData['cuaca'] ?? 'Cerah',
                    'action' => $this->getEspAction($espStatus)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get status for ESP error: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'status' => 'AMAN',
                'overall_status' => 'AMAN',
                'ph' => 7.0,
                'ph_status' => 'AMAN',
                'turbidity' => 30,
                'turbidity_status' => 'AMAN',
                'additional_info' => [
                    'message' => 'Sistem normal',
                    'action' => 'NORMAL'
                ]
            ]);
        }
    }
    
    private function determineOverallStatus($phStatus, $turbidityStatus)
    {
        $priority = [
            'bahaya' => 3,
            'peringatan' => 2,
            'aman' => 1,
            'baik' => 1
        ];
        
        $phPriority = $priority[$phStatus] ?? 1;
        $turbidityPriority = $priority[$turbidityStatus] ?? 1;
        
        if ($phPriority >= $turbidityPriority) {
            return $phStatus;
        }
        return $turbidityStatus;
    }
    
    private function getEspStatusMessage($status)
    {
        return match($status) {
            'BAHAYA' => 'BAHAYA! Kondisi air kritis. Hentikan pakan dan periksa kolam!',
            'PERINGATAN' => 'PERINGATAN! Kualitas air kurang baik. Kurangi pakan 50%',
            default => 'AMAN. Kualitas air normal. Operasikan normal.'
        };
    }
    
    private function getEspAction($status)
    {
        return match($status) {
            'BAHAYA' => 'STOP_ALL',
            'PERINGATAN' => 'REDUCE_FEED',
            default => 'NORMAL'
        };
    }
    
    // ==================== KALIBRASI OFFSET MANUAL (TAMBAHAN BARU) ====================
    
    /**
     * Set offset pH manual
     * POST /api/calibration/ph-offset
     */
    public function setPHOffset(Request $request)
    {
        try {
            $request->validate([
                'offset' => 'required|numeric|min:-3|max:3'
            ]);
            
            $offset = $request->offset;
            $calibratedBy = $request->ip() ?? 'API';
            
            SensorCalibration::setPHOffset($offset, $calibratedBy);
            
            $this->updateRealtimeWithCalibration();
            
            return response()->json([
                'success' => true,
                'message' => "✅ Offset pH berhasil disimpan: " . ($offset >= 0 ? "+" : "") . $offset,
                'ph_offset' => $offset
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan offset: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Set offset turbidity manual
     * POST /api/calibration/turbidity-offset
     */
    public function setTurbidityOffset(Request $request)
    {
        try {
            $request->validate([
                'offset' => 'required|numeric|min:-500|max:500'
            ]);
            
            $offset = $request->offset;
            $calibratedBy = $request->ip() ?? 'API';
            
            SensorCalibration::setTurbidityOffset($offset, $calibratedBy);
            
            $this->updateRealtimeWithCalibration();
            
            return response()->json([
                'success' => true,
                'message' => "✅ Offset Turbidity berhasil disimpan: " . ($offset >= 0 ? "+" : "") . $offset,
                'turbidity_offset' => $offset
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan offset: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reset kalibrasi offset
     * POST /api/calibration/reset
     */
    public function resetCalibrationOffset(Request $request)
    {
        try {
            $type = $request->type ?? 'all';
            $calibratedBy = $request->ip() ?? 'API';
            
            SensorCalibration::resetCalibration($type, $calibratedBy);
            
            $this->updateRealtimeWithCalibration();
            
            return response()->json([
                'success' => true,
                'message' => "✅ Kalibrasi {$type} berhasil direset"
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reset kalibrasi: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update realtime dengan nilai kalibrasi
     */
    private function updateRealtimeWithCalibration()
    {
        $sensor = DB::table('sensors')->latest()->first();
        
        if ($sensor) {
            $calibratedPh = SensorCalibration::getCalibratedValue($sensor->ph, 'ph');
            $calibratedTurbidity = SensorCalibration::getCalibratedValue($sensor->turbidity, 'turbidity');
            
            DB::table('sensor_realtime')->updateOrInsert(
                ['id' => 1],
                [
                    'ph' => $calibratedPh,
                    'turbidity' => $calibratedTurbidity,
                    'ph_status' => $sensor->status_ph ?? 'aman',
                    'turbidity_status' => $sensor->status_turbidity ?? 'baik',
                    'updated_at' => now()
                ]
            );
        }
        
        Cache::forget('sensor_realtime_display');
    }
}