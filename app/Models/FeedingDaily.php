<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedingDaily extends Model
{
    protected $fillable = [
        'tanggal',
        'target_gram',
        'ph',
        'turbidity',
        'status'
    ];
}
