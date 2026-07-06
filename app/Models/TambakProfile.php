<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TambakProfile extends Model
{
    protected $table = 'tambak_profile';
    
    protected $fillable = [
        'user_id',
        'nama_tambak',
        'lokasi',
        'luas',
        'tipe_tambak',
        'populasi',
        'populasi_awal',
        'avg_weight',
        'biomassa_udang',
        'tanggal_mulai_budidaya',
        'tanggal_tebar',
        'target_panen_kg',
        'target_size_gram',
        'density',
        'foto_tambak',
        'nomor_wa',
        'penjaga',
        'cuaca',
        'intensitas_hujan',
        // 🔥 KOLOM KALIBRASI (TAMBAHAN)
        'ph_raw',
        'ph_offset',
        'turbidity_raw',
        'turbidity_offset',
        'last_calibration_ph',
        'last_calibration_turbidity'
    ];
    
    protected $casts = [
        'tanggal_mulai_budidaya' => 'date',
        'tanggal_tebar' => 'date',
        'last_calibration_ph' => 'datetime',
        'last_calibration_turbidity' => 'datetime',
    ];
    
    /**
     * Relasi ke user (inverse)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Hitung umur budidaya (DOC - Day of Culture) dalam hari
     */
    public function getUmurHariAttribute()
    {
        if (!$this->tanggal_mulai_budidaya) {
            return 0;
        }
        return $this->tanggal_mulai_budidaya->diffInDays(now());
    }
    
    /**
     * Hitung umur dalam minggu
     */
    public function getUmurMingguAttribute()
    {
        return ceil($this->umur_hari / 7);
    }
    
    /**
     * Hitung survival rate (%)
     */
    public function getSurvivalRateAttribute()
    {
        if (!$this->populasi_awal || $this->populasi_awal == 0) {
            return 0;
        }
        return round(($this->populasi / $this->populasi_awal) * 100, 1);
    }
    
    /**
     * Hitung biomassa (kg)
     */
    public function getBiomassaKgAttribute()
    {
        return round(($this->populasi * $this->avg_weight) / 1000, 2);
    }
    
    /**
     * Hitung pertumbuhan harian (gram/hari)
     */
    public function getDailyGrowthRateAttribute()
    {
        if ($this->umur_hari == 0) return 0;
        return round($this->avg_weight / $this->umur_hari, 2);
    }
    
    /**
     * Hitung prediksi waktu panen (hari lagi)
     */
    public function getPrediksiPanenHariAttribute()
    {
        if ($this->target_size_gram <= 0 || $this->daily_growth_rate <= 0) {
            return 0;
        }
        $sisaPertumbuhan = $this->target_size_gram - $this->avg_weight;
        if ($sisaPertumbuhan <= 0) return 0;
        return ceil($sisaPertumbuhan / $this->daily_growth_rate);
    }
    
    /**
     * Format tanggal mulai budidaya
     */
    public function getTanggalMulaiFormattedAttribute()
    {
        if (!$this->tanggal_mulai_budidaya) return '-';
        return $this->tanggal_mulai_budidaya->format('d/m/Y');
    }
    
    // 🔥 ==================== METHOD KALIBRASI ====================
    
    /**
     * Get calibrated pH value
     */
    public function getCalibratedPhAttribute()
    {
        $raw = $this->ph_raw ?? 7.0;
        $offset = $this->ph_offset ?? 0;
        $value = $raw + $offset;
        // Batasi range pH 0-14
        return max(0, min(14, round($value, 2)));
    }
    
    /**
     * Get calibrated turbidity value (0-1000 NTU)
     */
    public function getCalibratedTurbidityAttribute()
    {
        $raw = $this->turbidity_raw ?? 30;
        $offset = $this->turbidity_offset ?? 0;
        $value = $raw + $offset;
        // Batasi range 0-1000 NTU
        return max(0, min(1000, round($value)));
    }
    
    /**
     * Get pH status based on value
     */
    public function getPhStatusAttribute()
    {
        $ph = $this->calibrated_ph;
        if ($ph >= 7.5 && $ph <= 8.5) return 'baik';
        if ($ph >= 7.0 && $ph < 7.5) return 'normal';
        if ($ph > 8.5 && $ph <= 9.0) return 'warning';
        if ($ph < 6.0 || $ph > 9.0) return 'danger';
        return 'normal';
    }
    
    /**
     * Get turbidity status based on value (0-1000 NTU)
     */
    public function getTurbidityStatusAttribute()
    {
        $turb = $this->calibrated_turbidity;
        if ($turb <= 50) return 'baik';
        if ($turb <= 100) return 'normal';
        if ($turb <= 200) return 'warning';
        return 'danger';
    }
    
    /**
     * Cek apakah pH sudah dikalibrasi
     */
    public function getIsPhCalibratedAttribute()
    {
        return !is_null($this->last_calibration_ph) && $this->ph_offset != 0;
    }
    
    /**
     * Cek apakah turbidity sudah dikalibrasi
     */
    public function getIsTurbidityCalibratedAttribute()
    {
        return !is_null($this->last_calibration_turbidity) && $this->turbidity_offset != 0;
    }
}