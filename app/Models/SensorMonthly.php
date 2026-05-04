<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorMonthly extends Model
{
    protected $table = 'sensor_monthly';

protected $fillable = [
    'avg_ph',
    'avg_turbidity',
    'month'
];
}
