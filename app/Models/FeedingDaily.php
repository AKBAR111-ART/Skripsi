<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedingDaily extends Model
{
    protected $fillable = [
        'tanggal',
        'ph',
        'turbidity',
        'biomassa',

        'rekomendasi_pagi',
        'real_pakan_pagi',

        'rekomendasi_siang',
        'real_pakan_siang',

        'rekomendasi_sore',
        'real_pakan_sore',

        'rekomendasi_malam',
        'real_pakan_malam',

        'total_rekomendasi',
        'total_real',
    ];
}
