<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TambakProfile extends Model
{
    protected $table = 'tambak_profile';
    
    protected $fillable = [
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
        'intensitas_hujan'
    ];
    
    protected $casts = [
        'tanggal_mulai_budidaya' => 'date',
        'tanggal_tebar' => 'date',
    ];
    
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
}