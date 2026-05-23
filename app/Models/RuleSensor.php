<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RuleSensor extends Model
{
    protected $table = 'rule_sensors';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // ========== pH ==========
        'ph_min_good',
        'ph_max_good',
        'ph_min_warning',
        'ph_max_warning',
        'ph_min_warning_high',
        'ph_max_warning_high',
        'ph_danger_low',
        'ph_danger_high',
        
        // ========== Turbidity (NTU) ==========
        'turbidity_min_good',
        'turbidity_max_good',
        'turbidity_min_warning',
        'turbidity_max_warning',
        'turbidity_danger_low',
        'turbidity_danger_high',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // pH (float)
        'ph_min_good' => 'float',
        'ph_max_good' => 'float',
        'ph_min_warning' => 'float',
        'ph_max_warning' => 'float',
        'ph_min_warning_high' => 'float',
        'ph_max_warning_high' => 'float',
        'ph_danger_low' => 'float',
        'ph_danger_high' => 'float',
        
        // Turbidity (integer)
        'turbidity_min_good' => 'integer',
        'turbidity_max_good' => 'integer',
        'turbidity_min_warning' => 'integer',
        'turbidity_max_warning' => 'integer',
        'turbidity_danger_low' => 'integer',
        'turbidity_danger_high' => 'integer',
    ];
    
    /**
     * Default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        // pH default
        'ph_min_good' => 7.5,
        'ph_max_good' => 8.5,
        'ph_min_warning' => 7.0,
        'ph_max_warning' => 9.0,
        'ph_min_warning_high' => 8.6,
        'ph_max_warning_high' => 8.9,
        'ph_danger_low' => 6.0,
        'ph_danger_high' => 9.5,
        
        // Turbidity default (NTU)
        'turbidity_min_good' => 0,
        'turbidity_max_good' => 50,
        'turbidity_min_warning' => 51,
        'turbidity_max_warning' => 100,
        'turbidity_danger_low' => 0,
        'turbidity_danger_high' => 100,
    ];
    
    /**
     * Get rule from cache (untuk performa)
     */
    public static function getCached()
    {
        return Cache::remember('sensor_rule', 600, function () {
            return self::first();
        });
    }
    
    /**
     * Clear rule cache
     */
    public static function clearCache()
    {
        Cache::forget('sensor_rule');
    }
    
    /**
     * Get pH status based on value
     */
    public function getPhStatus($value)
    {
        if ($value < $this->ph_danger_low || $value > $this->ph_danger_high) {
            return 'bahaya';
        }
        
        if ($value < $this->ph_min_good || $value > $this->ph_max_good) {
            return 'peringatan';
        }
        
        return 'aman';
    }
    
    /**
     * Get turbidity status based on value (NTU)
     */
    public function getTurbidityStatus($value)
    {
        if ($value === null) return 'aman';
        
        if ($value < $this->turbidity_min_good || $value > $this->turbidity_max_good) {
            if ($value < $this->turbidity_min_warning || $value > $this->turbidity_max_warning) {
                return 'bahaya';
            }
            return 'peringatan';
        }
        
        return 'aman';
    }
    
    /**
     * Update rule from request
     */
    public function updateRule(array $data)
    {
        $this->update($data);
        self::clearCache();
        
        return $this;
    }
    
    /**
     * Reset to default values
     */
    public function resetToDefault()
    {
        $defaults = [
            'ph_min_good' => 7.5,
            'ph_max_good' => 8.5,
            'ph_min_warning' => 7.0,
            'ph_max_warning' => 9.0,
            'ph_min_warning_high' => 8.6,
            'ph_max_warning_high' => 8.9,
            'ph_danger_low' => 6.0,
            'ph_danger_high' => 9.5,
            'turbidity_min_good' => 0,
            'turbidity_max_good' => 50,
            'turbidity_min_warning' => 51,
            'turbidity_max_warning' => 100,
            'turbidity_danger_low' => 0,
            'turbidity_danger_high' => 100,
        ];
        
        $this->update($defaults);
        self::clearCache();
        
        return $this;
    }
}