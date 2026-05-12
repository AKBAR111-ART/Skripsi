<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanTambak extends Model
{
    protected $table = 'pengaturan_tambak';

    protected $fillable = [
        'rule_sensor',
        'pengingat'
    ];

    protected $casts = [
        'rule_sensor' => 'array',
        'pengingat' => 'array',
    ];
}