<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cycle extends Model
{
    use HasFactory;
    
    protected $table = 'cycles';
    
    protected $fillable = [
        'name', 'start_date', 'end_date', 'total_weeks', 'pond',
        'status', 'target_harvest_kg', 'notes'
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'target_harvest_kg' => 'decimal:2'
    ];
    
    // Relasi ke MonitoringData
    public function monitoringData()
    {
        return $this->hasMany(MonitoringData::class, 'cycle_id');
    }
    
    // Relasi ke FeedingRecord
    public function feedingRecords()
    {
        return $this->hasMany(FeedingRecord::class, 'cycle_id');
    }
    
    // Relasi ke DailySummary
    public function dailySummaries()
    {
        return $this->hasMany(DailySummary::class, 'cycle_id');
    }
    
    // Relasi ke WeeklySummary
    public function weeklySummaries()
    {
        return $this->hasMany(WeeklySummary::class, 'cycle_id');
    }
    
    // Get active cycle
    public static function getActive()
    {
        return self::where('status', 'active')->first();
    }
}