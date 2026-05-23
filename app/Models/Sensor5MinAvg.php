<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Sensor5MinAvg extends Model
{
    protected $table = 'sensor_5min_avg';
    
    protected $fillable = [
        'avg_ph', 'avg_turbidity',
        'min_ph', 'max_ph',
        'min_turbidity', 'max_turbidity',
        'sample_count', 'status',
        'period_start', 'period_end',
        'date', 'time_slot'
    ];
    
    protected $casts = [
        'avg_ph' => 'float',
        'avg_turbidity' => 'float',
        'min_ph' => 'float',
        'max_ph' => 'float',
        'min_turbidity' => 'float',
        'max_turbidity' => 'float',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    // Get data untuk hari ini
    public static function getTodayData()
    {
        return self::whereDate('date', Carbon::today())
                    ->orderBy('time_slot', 'asc')
                    ->get();
    }
    
    // Get data untuk grafik
    public static function getChartData($date = null)
    {
        $targetDate = $date ? Carbon::parse($date) : Carbon::today();
        
        return self::whereDate('date', $targetDate)
                    ->orderBy('time_slot', 'asc')
                    ->get()
                    ->map(function($item) {
                        return [
                            'time' => $item->time_slot,
                            'ph' => $item->avg_ph,
                            'turbidity' => $item->avg_turbidity,
                            'min_ph' => $item->min_ph,
                            'max_ph' => $item->max_ph,
                            'status' => $item->status
                        ];
                    });
    }
    
    // Update status
    public static function updateStatus($avgPh, $avgTurbidity)
    {
        $phNormal = ($avgPh >= 7 && $avgPh <= 8);
        $turbNormal = ($avgTurbidity < 30);
        
        if ($phNormal && $turbNormal) return 'Normal';
        if (!$phNormal && !$turbNormal) return 'Kritis';
        return 'Sedang';
    }
}