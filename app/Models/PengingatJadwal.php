<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengingatJadwal extends Model
{
    protected $table = 'pengingat_jadwal';
    
    protected $fillable = [
        'pengingat_harian_id', 'jam', 'pesan', 'is_sent', 'target_nomor'
    ];
    
    protected $casts = [
        'is_sent' => 'boolean',
        'target_nomor' => 'array'
    ];
}