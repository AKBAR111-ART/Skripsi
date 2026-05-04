<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sensor5Min extends Model
{
    protected $table = 'sensor_5mins';

    protected $fillable = [
        'avg_ph',
        'avg_turbidity',
        'time_5min',
    ];
}