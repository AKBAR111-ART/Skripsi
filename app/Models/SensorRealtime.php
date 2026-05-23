<?php
// app/Models/SensorRealtime.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorRealtime extends Model
{
    protected $table = 'sensor_realtime';
    
    protected $fillable = [
        'ph', 
        'ph_status',
        'turbidity', 
        'turbidity_status'
    ];
    
    protected $casts = [
        'ph' => 'float',
        'turbidity' => 'float'
    ];
}