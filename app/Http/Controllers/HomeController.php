<?php
// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use App\Models\Rule;
use App\Models\RuleSensor;
use App\Models\TambakProfile;
use App\Models\SensorRealtime;
use App\Models\Sensor;
use App\Models\FeedingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class HomeController extends Controller
{
    /**
     * Display the home/dashboard page
     */
    public function index()
    {
        // 🔥 AMBIL DATA PROFILE TERBARU
        $profile = TambakProfile::first();
        
        // 🔥 AMBIL POPULASI TERBARU DARI DATABASE
        $populasi = $profile->populasi ?? 5000;
        $berat_rata = $profile->avg_weight ?? 15;
        $biomassa = ($populasi * $berat_rata); // dalam gram
        
        // 🔥 HITUNG UMUR (MINGGU)
        $umur_minggu = 1;
        if ($profile && $profile->tanggal_mulai_budidaya) {
            $start = Carbon::parse($profile->tanggal_mulai_budidaya);
            $umurHari = $start->diffInDays(now());
            $umur_minggu = max(1, ceil($umurHari / 7));
        }
        
        // 🔥 HITUNG PAKAN HARI INI
        $pakanHariIni = FeedingRecord::whereDate('created_at', today())->sum('pakan_kg');
        
        // 🔥 AMBIL RULE TERBARU
        $rule = RuleSensor::first();
        if (!$rule) {
            $rule = RuleSensor::create([
                'ph_min_good' => 7.5,
                'ph_max_good' => 8.5,
                'ph_min_warning' => 7.0,
                'ph_max_warning' => 7.4,
                'ph_min_warning_high' => 8.6,
                'ph_max_warning_high' => 8.9,
                'ph_danger_low' => 6.5,
                'ph_danger_high' => 9.0,
                'turbidity_min_good' => 25,
                'turbidity_max_good' => 50,
                'turbidity_min_warning' => 51,
                'turbidity_max_warning' => 70,
                'turbidity_danger_low' => 10,
                'turbidity_danger_high' => 70,
            ]);
        }
        
        // Data tambak untuk view
        $tambak = [
            'nama' => $profile->nama_tambak ?? 'Tambak Mandhala',
            'luas' => $profile->luas ?? 2000,
            'populasi' => $populasi,
            'berat_rata' => $berat_rata,
            'biomassa' => $biomassa,
            'umur_minggu' => $umur_minggu
        ];
        
        return view('dashboard.home', compact('pakanHariIni', 'umur_minggu', 'berat_rata', 'biomassa', 'rule', 'tambak', 'populasi'));
    }
    
    /**
     * Get realtime sensor data (API endpoint)
     */
    public function getRealtimeData()
{
    try {
        // Ambil dari sensor_realtime
        $sensor = SensorRealtime::first();
        
        if (!$sensor) {
            $sensor = Sensor::latest()->first();
        }
        
        if (!$sensor) {
            // Fallback data jika belum ada sensor
            return response()->json([
                'ph' => 7.5,
                'ph_status' => 'baik',
                'turbidity' => 30,
                'turbidity_status' => 'baik',
                'source' => 'fallback'
            ]);
        }
        
        // Hitung status berdasarkan rule jika perlu
        $rule = RuleSensor::first();
        
        $phStatus = $this->getPhStatus($sensor->ph, $rule);
        $turbidityStatus = $this->getTurbidityStatus($sensor->turbidity, $rule);
        
        return response()->json([
            'ph' => (float)$sensor->ph,
            'ph_status' => $phStatus,
            'turbidity' => (float)$sensor->turbidity,
            'turbidity_status' => $turbidityStatus,
            'source' => 'database',
            'last_update' => $sensor->created_at
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'ph' => 7.5,
            'ph_status' => 'baik',
            'turbidity' => 30,
            'turbidity_status' => 'baik',
            'error' => $e->getMessage()
        ]);
    }
}
private function getPhStatus($ph, $rule = null)
{
    if (!$rule) {
        $rule = RuleSensor::first();
    }
    
    if ($rule) {
        if ($ph <= $rule->ph_danger_low || $ph >= $rule->ph_danger_high) {
            return 'bahaya';
        }
        if (($ph >= $rule->ph_min_warning && $ph <= $rule->ph_max_warning) || 
            ($ph >= $rule->ph_min_warning_high && $ph <= $rule->ph_max_warning_high)) {
            return 'peringatan';
        }
    }
    
    // Default rule
    if ($ph < 6.5 || $ph > 9.0) return 'bahaya';
    if ($ph < 7.0 || $ph > 8.5) return 'peringatan';
    return 'baik';
}

/**
 * Get turbidity status based on rules
 */
private function getTurbidityStatus($turbidity, $rule = null)
{
    if (!$rule) {
        $rule = RuleSensor::first();
    }
    
    if ($rule) {
        if ($turbidity <= $rule->turbidity_danger_low || $turbidity >= $rule->turbidity_danger_high) {
            return 'bahaya';
        }
        if ($turbidity >= $rule->turbidity_min_warning && $turbidity <= $rule->turbidity_max_warning) {
            return 'peringatan';
        }
    }
    
    // Default rule
    if ($turbidity < 10 || $turbidity > 70) return 'bahaya';
    if ($turbidity > 50) return 'peringatan';
    return 'baik';
}
    
    /**
     * Get latest profile data (API endpoint)
     */
    public function getLatestProfileData()
    {
        $profile = TambakProfile::first();
        
        // Hitung umur
        $umur_minggu = 1;
        if ($profile && $profile->tanggal_mulai_budidaya) {
            $start = Carbon::parse($profile->tanggal_mulai_budidaya);
            $umurHari = $start->diffInDays(now());
            $umur_minggu = max(1, ceil($umurHari / 7));
        }
        
        // Hitung biomassa
        $populasi = $profile->populasi ?? 5000;
        $avg_weight = $profile->avg_weight ?? 15;
        $biomassaKg = round(($populasi * $avg_weight) / 1000, 2);
        
        return response()->json([
            'success' => true,
            'populasi' => $populasi,
            'avg_weight' => $avg_weight,
            'biomassa_kg' => $biomassaKg,
            'umur_minggu' => $umur_minggu
        ]);
    }
    
    /**
     * Send feed command (API endpoint)
     */
  public function sendPakan(Request $request)
{
    try {
        // Ambil input (support kedua nama)
        $pakanGram = $request->input('target_gram') ?? $request->input('pakan');
        
        // Validasi
        if (!$pakanGram || $pakanGram <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pakan tidak valid. Minimal 1 gram.'
            ]);
        }
        
        $jadwal = $request->input('jadwal', $this->getJadwalByTime());
        $sumber = $request->input('sumber', 'manual');
        $pakanKg = round($pakanGram / 1000, 2);
        
        // Simpan ke database
        $feedingRecord = FeedingRecord::create([
            'pakan_kg' => $pakanKg,
            'target_gram' => $pakanGram,
            'jadwal' => $jadwal,
            'sumber' => $sumber,
            'status' => 'success',
            'waktu_pemberian' => now()->format('H:i:s'),
            'keterangan' => "Pemberian pakan via {$sumber}"
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "✅ Pakan {$pakanGram} gram ({$pakanKg} kg) berhasil dikirim!",
            'data' => $feedingRecord
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error sendPakan: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim pakan: ' . $e->getMessage()
        ], 500);
    }
}
    
    /**
     * Get jadwal berdasarkan jam
     */
    private function getJadwalByTime()
    {
        $jam = (int)date('H');
        if ($jam >= 5 && $jam < 11) return 'pagi';
        if ($jam >= 11 && $jam < 15) return 'siang';
        return 'sore';
    }
    
    /**
     * Get latest sensor data
     */
    public function latest()
    {
        $sensor = SensorRealtime::first();
        
        return response()->json([
            'success' => true,
            'data' => [
                'ph' => $sensor->ph ?? 7.5,
                'turbidity' => $sensor->turbidity ?? 30,
                'created_at' => now()
            ]
        ]);
    }
    
    /**
     * Realtime sensor page
     */
    public function realtime()
    {
        return view('sensor.realtime');
    }
        /**
     * Get feeding recommendation (API endpoint)
     */
    public function getFeedingRecommendation()
    {
        try {
            $profile = TambakProfile::first();
            $sensor = SensorRealtime::first();
            
            if (!$sensor) {
                $sensor = Sensor::latest()->first();
            }
            
            $populasi = $profile->populasi ?? 5000;
            $avgWeight = $profile->avg_weight ?? 15;
            $biomassaGram = $populasi * $avgWeight;
            $biomassaKg = $biomassaGram / 1000;
            
            $feedingRate = 0.03;
            
            if ($profile && $profile->tanggal_mulai_budidaya) {
                $start = Carbon::parse($profile->tanggal_mulai_budidaya);
                $umurHari = $start->diffInDays(now());
                $umurMinggu = ceil($umurHari / 7);
                
                if ($umurMinggu <= 4) {
                    $feedingRate = 0.05;
                } elseif ($umurMinggu <= 8) {
                    $feedingRate = 0.04;
                } else {
                    $feedingRate = 0.03;
                }
            }
            
            $estimasiGram = $biomassaGram * $feedingRate;
            
            $faktorKoreksi = 1.0;
            if ($sensor) {
                if ($sensor->ph < 6.5 || $sensor->ph > 8.5) {
                    $faktorKoreksi *= 0.5;
                } elseif ($sensor->ph < 7.0 || $sensor->ph > 8.0) {
                    $faktorKoreksi *= 0.75;
                }
                
                if ($sensor->turbidity > 70) {
                    $faktorKoreksi *= 0.5;
                } elseif ($sensor->turbidity > 50) {
                    $faktorKoreksi *= 0.75;
                }
            }
            
            $rekomendasiGram = round($estimasiGram * $faktorKoreksi, 0);
            $rekomendasiKg = round($rekomendasiGram / 1000, 2);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'pakan_rekomendasi_gram' => $rekomendasiGram,
                    'pakan_rekomendasi_kg' => $rekomendasiKg,
                    'biomassa_kg' => $biomassaKg,
                    'feeding_rate' => $feedingRate * 100,
                    'faktor_koreksi' => $faktorKoreksi
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's total feeding
     */
    public function getTodayFeeding()
    {
        $totalKg = FeedingRecord::whereDate('created_at', today())->sum('pakan_kg');
        
        return response()->json([
            'success' => true,
            'total_kg' => $totalKg,
            'total_gram' => $totalKg * 1000
        ]);
    }
    public function sendFeedCommand(Request $request)
{
    try {
        $pakanGram = $request->input('target_gram');
        $jadwal = $request->input('jadwal', 'sore');
        $sumber = $request->input('sumber', 'manual');
        
        if (!$pakanGram || $pakanGram <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pakan tidak valid'
            ]);
        }
        
        $pakanKg = round($pakanGram / 1000, 2);
        
        // Simpan ke database
        $record = \App\Models\FeedingRecord::create([
            'pakan_kg' => $pakanKg,
            'target_gram' => $pakanGram,
            'jadwal' => $jadwal,
            'sumber' => $sumber,
            'status' => 'success',
            'waktu_pemberian' => now()->format('H:i:s'),
            'keterangan' => "Pemberian pakan via {$sumber}"
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "✅ Pakan {$pakanGram} gram ({$pakanKg} kg) berhasil dikirim!",
            'data' => $record
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim pakan: ' . $e->getMessage()
        ], 500);
    }
}
}