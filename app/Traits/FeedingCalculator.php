<?php
// app/Traits/FeedingCalculator.php

namespace App\Traits;

use App\Services\WeatherService;
use App\Models\TambakProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait FeedingCalculator
{
    /**
     * Hitung rekomendasi pakan dengan standar yang sama
     * 🔥 SUMBER DATA: TAMBAK_PROFILE
     */
    protected function calculateFeed($profile, $sensor)
    {
        // Jika profile null, coba ambil dari TambakProfile
        if (!$profile) {
            $profile = TambakProfile::first();
        }
        
        // Jika masih null, return default
        if (!$profile) {
            return $this->getDefaultFeed();
        }

        // 🔥 AMBIL DATA DARI TAMBAK_PROFILE
        $populasi = $profile->populasi ?? 5000;
        $beratRata = $profile->avg_weight ?? 15;
        
        // 🔥 HITUNG BIOMASSA (KG)
        $biomassaKg = round(($populasi * $beratRata) / 1000, 2);
        
        // 🔥 HITUNG UMUR (HARI) DARI TANGGAL_MULAI_BUDIDAYA
        $umurHari = 0;
        if ($profile->tanggal_mulai_budidaya) {
            $start = Carbon::parse($profile->tanggal_mulai_budidaya);
            $umurHari = $start->diffInDays(now());
        }
        
        // 🔥 FEEDING RATE BERDASARKAN UMUR
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
        
        // 🔥 PAKAN DASAR
        $pakanDasarKg = round($biomassaKg * $feedingRate, 2);
        
        // 🔥 FAKTOR KOREKSI DARI SENSOR (KONDISI AIR)
        $faktorAir = $this->getAirFactor($sensor);
        
        // 🔥 FAKTOR KOREKSI DARI CUACA
        $faktorCuaca = $this->getCuacaFactor();
        
        // 🔥 PAKAN AKHIR
        $pakanAkhir = round($pakanDasarKg * $faktorAir * $faktorCuaca, 2);
        
        // Minimal 0.1 kg
        if ($pakanAkhir < 0.1) {
            $pakanAkhir = 0.1;
        }
        
        return [
            'pakan_rekomendasi_kg' => $pakanAkhir,
            'pakan_rekomendasi_gram' => round($pakanAkhir * 1000, 0),
            'pakan_dasar_kg' => $pakanDasarKg,
            'biomassa_kg' => $biomassaKg,
            'umur_hari' => $umurHari,
            'umur_minggu' => ceil($umurHari / 7),
            'tanggal_mulai' => $profile->tanggal_mulai_budidaya ? Carbon::parse($profile->tanggal_mulai_budidaya)->format('d/m/Y') : '-',
            'populasi' => $populasi,
            'berat_rata' => $beratRata,
            'feeding_rate' => round($feedingRate * 100, 1),
            'faktor_air' => $faktorAir,
            'faktor_cuaca' => $faktorCuaca,
            'status_keseluruhan' => $this->getOverallStatus($faktorAir),
            'keterangan' => $this->getKeterangan($faktorAir, $sensor)
        ];
    }
    
    /**
     * Hitung faktor koreksi dari kondisi air
     * 🔥 MEMBACA STATUS DARI DATABASE, BUKAN MENGHITUNG ULANG!
     */
    private function getAirFactor($sensor)
    {
        if (!$sensor) {
            return 1.0;
        }
        
        // 🔥 AMBIL STATUS YANG SUDAH TERSIMPAN DI DATABASE
        $phStatus = $sensor->ph_status ?? 'baik';
        $turbidityStatus = $sensor->turbidity_status ?? 'baik';
        
        // 🔥 TENTUKAN FAKTOR BERDASARKAN STATUS (BUKAN NILAI)
        if ($phStatus === 'bahaya' || $turbidityStatus === 'bahaya') {
            return 0; // Stop pakan
        }
        
        if ($phStatus === 'peringatan' || $turbidityStatus === 'peringatan') {
            return 0.5; // Kurangi 50%
        }
        
        // Kondisi normal
        return 1.0;
    }
    
    /**
     * Hitung faktor koreksi dari cuaca
     */
    private function getCuacaFactor()
    {
        try {
            $weatherService = app(WeatherService::class);
            $weather = $weatherService->getWeatherSumenep();
            
            if ($weather['success'] && isset($weather['feed_recommendation'])) {
                $adjustment = $weather['feed_recommendation']['adjustment'] ?? 0;
                return 1 + ($adjustment / 100);
            }
        } catch (\Exception $e) {
            // Jika gagal, return 1
        }
        
        return 1.0;
    }
    
    /**
     * Get status keseluruhan
     */
    private function getOverallStatus($faktorAir)
    {
        if ($faktorAir == 0) return 'bahaya';
        if ($faktorAir < 1) return 'peringatan';
        return 'normal';
    }
    
    /**
     * Get keterangan
     */
    private function getKeterangan($faktorAir, $sensor)
    {
        if ($faktorAir == 0) {
            return '🔴 KONDISI BAHAYA! Hentikan pemberian pakan sementara.';
        }
        
        if ($faktorAir < 1) {
            return '⚠️ Kualitas air kurang baik. Kurangi pakan 50%.';
        }
        
        if ($sensor) {
            $ph = $sensor->ph ?? 7.0;
            $turbidity = $sensor->turbidity ?? 30;
            return "✅ Kualitas air optimal (pH: {$ph}, NTU: {$turbidity})";
        }
        
        return '✅ Kualitas air optimal. Berikan pakan sesuai jadwal.';
    }
    
    /**
     * Get default feed
     */
    private function getDefaultFeed()
    {
        return [
            'pakan_rekomendasi_kg' => 0.5,
            'pakan_rekomendasi_gram' => 500,
            'pakan_dasar_kg' => 0.5,
            'biomassa_kg' => 75,
            'umur_hari' => 0,
            'umur_minggu' => 0,
            'tanggal_mulai' => '-',
            'populasi' => 5000,
            'berat_rata' => 15,
            'feeding_rate' => 3.5,
            'faktor_air' => 1.0,
            'faktor_cuaca' => 1.0,
            'status_keseluruhan' => 'normal',
            'keterangan' => 'Data default - silakan update profile tambak'
        ];
    }
    
    /**
     * Get status text untuk pH
     */
    protected function getStatusText($ph, $rule = null)
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
     * Get status text untuk turbidity
     */
    protected function getTurbidityStatusText($turbidity, $rule = null)
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
}