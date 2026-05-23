<?php
// app/Models/Pengaturan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';
    
    protected $fillable = [
        'penjaga',
        'nomor_wa',
        'waktu',
        'tanggal',
        'template_pesan'
    ];
    
    protected $casts = [
        'penjaga' => 'array',
        'nomor_wa' => 'array',
        'waktu' => 'array',
        'tanggal' => 'date'
    ];
}