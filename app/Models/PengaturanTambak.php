<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanTambak extends Model
{
    protected $table = 'pengaturan_tambak';
    
    protected $fillable = [
        'nomor_wa',
        'whatsapp_aktif',
        'rule_engine_aktif',
        'penjaga',
        'waktu',
        'tanggal',
        'template_pesan',
        'populasi',
        'berat_rata',
        'umur_minggu',
        'pakan_per_ekor'
    ];
    
    protected $casts = [
        'penjaga' => 'array',
        'nomor_wa' => 'array',
        'waktu' => 'array',
        'whatsapp_aktif' => 'boolean',
        'rule_engine_aktif' => 'boolean'
    ];
}