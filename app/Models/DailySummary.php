<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailySummary extends Model
{
    use HasFactory;
    
    protected $table = 'daily_summaries';
    
    protected $fillable = [
        'cycle_id', 'date', 'week', 'day_name', 'avg_ph', 'min_ph', 'max_ph',
        'avg_turbidity', 'min_turbidity', 'max_turbidity', 'total_feed',
        'feeding_count', 'status', 'notes'
    ];
    
    protected $casts = [
        'date' => 'date',
        'avg_ph' => 'decimal:1',
        'min_ph' => 'decimal:1',
        'max_ph' => 'decimal:1',
        'total_feed' => 'decimal:2'
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
            'Baik' => '<span class="status-badge good">✅ Baik</span>',
            'Perhatian' => '<span class="status-badge warning">⚠️ Perhatian</span>',
            'Bahaya' => '<span class="status-badge danger">🔴 Bahaya</span>',
            default => '<span class="status-badge">' . $this->status . '</span>'
        };
    }
}