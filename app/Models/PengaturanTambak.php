<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanTambak extends Model
{
    protected $table = 'rule_sensor_air';

    protected $fillable = [

        'ph_min_good',
        'ph_max_good',

        'ph_min_warning',
        'ph_max_warning',

        'ph_danger_low',
        'ph_danger_high',

        'turbidity_min_good',
        'turbidity_max_good',

        'turbidity_min_warning',
        'turbidity_max_warning',

        'turbidity_danger_low',
        'turbidity_danger_high',

        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];
}