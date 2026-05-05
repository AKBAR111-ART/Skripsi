<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorDaily extends Model
{
    protected $table = 'sensor_daily';

    protected $fillable = [
        'avg_ph',
        'avg_turbidity',
        'date'
    ];
}
