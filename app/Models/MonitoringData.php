<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class MonitoringData extends Model
{
    use HasFactory;
    
    protected $table = 'monitoring_data';
    
    protected $fillable = [
        'ph', 
        'turbidity', 
        'status', 
        'recorded_at',
        'suhu',
        'salinitas',
        'do',
        'sumber',
        'keterangan',
        'cycle_id',
        'week',
        'date',
        'time',
        'day_name',
        'temperature',
        'salinity',
        'notes'
    ];
    
    protected $casts = [
        'ph' => 'float',
        'turbidity' => 'float',
        'recorded_at' => 'datetime',
        'date' => 'date',
        'time' => 'datetime:H:i'
    ];
    
    // ========== SCOPES ==========
    
    // Scope untuk hari ini
    public function scopeToday($query)
    {
        return $query->whereDate('recorded_at', Carbon::today());
    }
    
    // Scope untuk filter by week (untuk history)
    public function scopeWeek($query, $week)
    {
        return $query->where('week', $week);
    }
    
    // Scope untuk filter by date
    public function scopeDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }
    
    // Scope untuk filter by cycle
    public function scopeByCycle($query, $cycleId)
    {
        return $query->where('cycle_id', $cycleId);
    }
    
    // Scope untuk range tanggal
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
    
    // ========== STATIC METHODS ==========
    
    // Hitung rata-rata pH hari ini
    public static function avgPhToday()
    {
        $avg = self::whereDate('recorded_at', Carbon::today())->avg('ph');
        return $avg ? round($avg, 2) : 7.5;
    }
    
    // Hitung rata-rata kekeruhan hari ini
    public static function avgTurbidityToday()
    {
        $avg = self::whereDate('recorded_at', Carbon::today())->avg('turbidity');
        return $avg ? round($avg, 1) : 30;
    }
    
    // Rata-rata pH per minggu untuk history
    public static function avgPhPerWeek($cycleId, $week)
    {
        $avg = self::where('cycle_id', $cycleId)
                    ->where('week', $week)
                    ->avg('ph');
        return $avg ? round($avg, 1) : 7.0;
    }
    
    // Rata-rata kekeruhan per minggu untuk history
    public static function avgTurbidityPerWeek($cycleId, $week)
    {
        $avg = self::where('cycle_id', $cycleId)
                    ->where('week', $week)
                    ->avg('turbidity');
        return $avg ? round($avg, 0) : 12;
    }
    
    // Rata-rata pH per hari untuk history
    public static function avgPhPerDay($cycleId, $date)
    {
        $avg = self::where('cycle_id', $cycleId)
                    ->whereDate('date', $date)
                    ->avg('ph');
        return $avg ? round($avg, 1) : 7.0;
    }
    
    // Rata-rata kekeruhan per hari untuk history
    public static function avgTurbidityPerDay($cycleId, $date)
    {
        $avg = self::where('cycle_id', $cycleId)
                    ->whereDate('date', $date)
                    ->avg('turbidity');
        return $avg ? round($avg, 0) : 12;
    }
    
    // Data per jam untuk detail hari
    public static function getHourlyData($cycleId, $date)
    {
        return self::where('cycle_id', $cycleId)
                    ->whereDate('date', $date)
                    ->orderBy('time')
                    ->get()
                    ->map(function($item) {
                        return [
                            'time' => $item->time ? date('H:i', strtotime($item->time)) : '-',
                            'ph' => $item->ph,
                            'turbidity' => $item->turbidity,
                            'status' => $item->status,
                            'status_badge' => self::getStatusBadge($item->status)
                        ];
                    });
    }
    
    // Data terbaru
    public static function latestData()
    {
        return self::whereDate('recorded_at', Carbon::today())
                    ->orderBy('recorded_at', 'desc')
                    ->first();
    }
    
    // Tentukan status berdasarkan pH dan turbidity
    public static function determineStatus($ph, $turbidity)
    {
        $phNormal = ($ph >= 7 && $ph <= 8);
        $turbNormal = ($turbidity < 30);
        
        if ($phNormal && $turbNormal) {
            return 'normal';
        } elseif (!$phNormal && !$turbNormal) {
            return 'danger';
        } else {
            return 'warning';
        }
    }
    
    // Status kualitas air untuk text
    public static function getWaterQualityStatus($ph, $turbidity)
    {
        if ($ph >= 7 && $ph <= 8 && $turbidity < 30) {
            return 'Terpantau - Stabil';
        } elseif ($ph < 7) {
            return 'Terpantau - pH Rendah';
        } elseif ($ph > 8) {
            return 'Terpantau - pH Tinggi';
        } elseif ($turbidity >= 30 && $turbidity < 60) {
            return 'Terpantau - Air Keruh';
        } elseif ($turbidity >= 60) {
            return 'Terpantau - Air Sangat Keruh';
        }
        return 'Terpantau';
    }
    
    // Get status badge HTML
    public static function getStatusBadge($status)
    {
        return match($status) {
            'normal' => '<span class="status-badge good">✅ Normal</span>',
            'warning' => '<span class="status-badge warning">⚠️ Perhatian</span>',
            'danger' => '<span class="status-badge danger">🔴 Bahaya</span>',
            default => '<span class="status-badge">' . $status . '</span>'
        };
    }
    
    // Get all monitoring data for history by week
    public static function getHistoryByWeek($cycleId, $week)
    {
        return self::where('cycle_id', $cycleId)
                    ->where('week', $week)
                    ->orderBy('date')
                    ->orderBy('time')
                    ->get()
                    ->groupBy('date')
                    ->map(function($items, $date) {
                        return [
                            'date' => $date,
                            'day_name' => $items->first()->day_name ?? Carbon::parse($date)->locale('id')->isoFormat('dddd'),
                            'avg_ph' => round($items->avg('ph'), 1),
                            'avg_turbidity' => round($items->avg('turbidity'), 0),
                            'min_ph' => round($items->min('ph'), 1),
                            'max_ph' => round($items->max('ph'), 1),
                            'min_turbidity' => round($items->min('turbidity'), 0),
                            'max_turbidity' => round($items->max('turbidity'), 0),
                            'status' => self::getDayStatus($items->avg('ph'), $items->avg('turbidity')),
                            'hourly_data' => $items->map(function($item) {
                                return [
                                    'time' => $item->time ? date('H:i', strtotime($item->time)) : '-',
                                    'ph' => $item->ph,
                                    'turbidity' => $item->turbidity,
                                    'status' => $item->status
                                ];
                            })
                        ];
                    });
    }
    
    private static function getDayStatus($avgPh, $avgTurb)
    {
        if ($avgPh >= 6.8 && $avgPh <= 7.5 && $avgTurb <= 25) {
            return 'Baik';
        } elseif ($avgPh < 6.5 || $avgPh > 8.0 || $avgTurb > 35) {
            return 'Perhatian';
        }
        return 'Normal';
    }

    // ========== RELATIONSHIPS ==========
    
    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }
}