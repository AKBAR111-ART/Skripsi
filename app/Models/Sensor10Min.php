<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sensor10Min extends Model
{
  protected $fillable = ['avg_ph','avg_turbidity','time_10min'];
}
