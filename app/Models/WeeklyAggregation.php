<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WeeklyAggregation extends Model
{
    use HasFactory;
    
    protected $table = 'weekly_aggregations';
    
    protected $fillable = [
        'tambak_profile_id', 'year', 'week', 'week_start_date', 'week_end_date',
        'avg_ph', 'min_ph', 'max_ph', 'avg_turbidity', 'min_turbidity', 'max_turbidity',
        'total_feed_kg', 'total_recordings', 'status'
    ];
    
    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'avg_ph' => 'decimal:1'
    ];
    
    public function tambakProfile()
    {
        return $this->belongsTo(TambakProfile::class, 'tambak_profile_id');
    }
}