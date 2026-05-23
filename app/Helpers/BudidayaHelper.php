<?php

namespace App\Helpers;

use App\Models\TambakProfile;
use Carbon\Carbon;

class BudidayaHelper
{
    private static $profile = null;
    
    /**
     * Get active tambak profile (first one)
     */
    public static function getProfile()
    {
        if (self::$profile === null) {
            self::$profile = TambakProfile::first();
        }
        return self::$profile;
    }
    
    /**
     * Get tanggal mulai budidaya
     */
    public static function getTanggalMulai()
    {
        $profile = self::getProfile();
        return $profile ? $profile->tanggal_mulai_budidaya : null;
    }
    
    /**
     * Get umur budidaya dalam hari
     */
    public static function getUmurHari()
    {
        $start = self::getTanggalMulai();
        if (!$start) return 0;
        
        return Carbon::parse($start)->diffInDays(now());
    }
    
    /**
     * Get umur budidaya dalam minggu
     */
    public static function getUmurMinggu()
    {
        $hari = self::getUmurHari();
        return max(1, ceil($hari / 7));
    }
    
    /**
     * Get tanggal panen (90 hari setelah mulai)
     */
    public static function getTanggalPanen()
    {
        $start = self::getTanggalMulai();
        if (!$start) return null;
        
        return Carbon::parse($start)->addDays(90);
    }
    
    /**
     * Get progress persentase (hari ini / 90 hari)
     */
    public static function getProgressPersen()
    {
        $hari = self::getUmurHari();
        $total = 90;
        return min(($hari / $total) * 100, 100);
    }
    
    /**
     * Get total minggu budidaya (berdasarkan umur)
     */
    public static function getTotalMinggu()
    {
        $minggu = self::getUmurMinggu();
        // Tambahkan 4 minggu untuk prediksi ke depan
        return min($minggu + 4, 16);
    }
    
    /**
     * Get minggu saat ini
     */
    public static function getMingguSekarang()
    {
        return self::getUmurMinggu();
    }
    
    /**
     * Get data lengkap budidaya untuk berbagai halaman
     */
    public static function getAllData()
    {
        $profile = self::getProfile();
        $tanggalMulai = self::getTanggalMulai();
        $tanggalPanen = self::getTanggalPanen();
        
        return [
            'profile' => $profile,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_panen' => $tanggalPanen,
            'tanggal_panen_format' => $tanggalPanen ? $tanggalPanen->format('d M Y') : '-',
            'umur_hari' => self::getUmurHari(),
            'umur_minggu' => self::getUmurMinggu(),
            'progress_persen' => self::getProgressPersen(),
            'total_minggu' => self::getTotalMinggu(),
            'minggu_sekarang' => self::getMingguSekarang(),
            'populasi' => $profile->populasi ?? 0,
            'avg_weight' => $profile->avg_weight ?? 0,
            'biomassa_kg' => $profile ? (($profile->populasi ?? 0) * ($profile->avg_weight ?? 0)) / 1000 : 0
        ];
    }
}