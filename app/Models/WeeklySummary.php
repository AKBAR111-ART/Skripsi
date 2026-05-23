<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WeeklySummary extends Model
{
    use HasFactory;
    
    protected $table = 'weekly_summaries';
    
    protected $fillable = [
        'cycle_id', 'week', 'week_start_date', 'week_end_date', 'period',
        'avg_ph', 'min_ph', 'max_ph', 'avg_turbidity', 'min_turbidity',
        'max_turbidity', 'total_feed', 'total_feeding_count', 'status',
        'success_rate', 'insights'
    ];
    
    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'avg_ph' => 'decimal:1',
        'min_ph' => 'decimal:1',
        'max_ph' => 'decimal:1',
        'total_feed' => 'decimal:2',
        'success_rate' => 'decimal:2'
    ];
    
    // Relasi ke Cycle
    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }
    
    // Accessor untuk status badge
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'Normal' => '<span class="status-badge good">✅ Normal</span>',
            'Perhatian' => '<span class="status-badge warning">⚠️ Perhatian</span>',
            'Panen' => '<span class="status-badge harvest">🎯 Panen</span>',
            default => '<span class="status-badge">' . $this->status . '</span>'
        };
    }
}