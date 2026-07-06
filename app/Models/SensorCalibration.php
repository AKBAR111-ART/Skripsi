<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SensorCalibration extends Model
{
    protected $table = 'sensor_calibration';
    
    protected $fillable = [
        'ph_offset',
        'turbidity_offset',
        'is_calibrated',
        'last_calibration',
        'noise_level'
    ];
    
    protected $casts = [
        'ph_offset' => 'float',
        'turbidity_offset' => 'float',
        'is_calibrated' => 'boolean',
        'last_calibration' => 'datetime'
    ];
    
    /**
     * 🔥 SET OFFSET pH
     */
    public static function setPHOffset($offset, $calibratedBy = 'manual')
    {
        $calib = self::first() ?? new self();
        $calib->ph_offset = $offset;
        $calib->is_calibrated = true;
        $calib->last_calibration = now();
        $calib->save();
        
        // 🔥 Update sensor_realtime dengan nilai baru
        self::updateSensorRealtime();
        
        Cache::forget('sensor_calibration_data');
        Cache::forget('sensor_realtime_display');
        
        return $calib;
    }
    
    /**
     * 🔥 SET OFFSET TURBIDITY
     */
    public static function setTurbidityOffset($offset, $calibratedBy = 'manual')
    {
        $calib = self::first() ?? new self();
        $calib->turbidity_offset = $offset;
        $calib->is_calibrated = true;
        $calib->last_calibration = now();
        $calib->save();
        
        // 🔥 Update sensor_realtime dengan nilai baru
        self::updateSensorRealtime();
        
        Cache::forget('sensor_calibration_data');
        Cache::forget('sensor_realtime_display');
        
        return $calib;
    }
    
    /**
     * 🔥 UPDATE SENSOR_REALTIME DENGAN NILAI TERKALIBRASI + STATUS
     */
    /**
 * 🔥 UPDATE SENSOR_REALTIME DENGAN NILAI TERKALIBRASI
 */
private static function updateSensorRealtime()
{
    $sensor = DB::table('sensor_realtime')->first();
    if (!$sensor) {
        return;
    }
    
    $calib = self::first();
    if (!$calib) {
        return;
    }
    
    // 🔥 Nilai Akhir = Nilai Mentah + Offset
    $raw_ph = $sensor->ph - ($calib->ph_offset ?? 0);
    $raw_turbidity = $sensor->turbidity - ($calib->turbidity_offset ?? 0);
    
    $final_ph = $raw_ph + ($calib->ph_offset ?? 0);
    $final_turbidity = $raw_turbidity + ($calib->turbidity_offset ?? 0);
    
    $final_ph = max(0, min(14, $final_ph));
    $final_turbidity = max(0, min(1000, $final_turbidity));
    
    $rule = RuleSensor::first();
    $phStatus = self::getPhStatus($final_ph, $rule);
    $turbidityStatus = self::getTurbidityStatus($final_turbidity, $rule);
    
    DB::table('sensor_realtime')->update([
        'ph' => round($final_ph, 2),
        'turbidity' => round($final_turbidity, 0),
        'ph_status' => $phStatus,
        'turbidity_status' => $turbidityStatus,
        'updated_at' => now()
    ]);
}
    
    /**
     * Get pH status
     */
    private static function getPhStatus($ph, $rule = null)
    {
        if (!$rule) {
            $rule = RuleSensor::first();
        }
        
        if ($rule) {
            if ($ph <= $rule->ph_danger_low || $ph >= $rule->ph_danger_high) {
                return 'bahaya';
            }
            if (($ph >= $rule->ph_min_warning && $ph <= $rule->ph_max_warning) || 
                ($ph >= $rule->ph_min_warning_high && $ph <= $rule->ph_max_warning_high)) {
                return 'peringatan';
            }
            if ($ph >= $rule->ph_min_good && $ph <= $rule->ph_max_good) {
                return 'baik';
            }
        }
        
        if ($ph < 6.5 || $ph > 9.0) return 'bahaya';
        if ($ph < 7.0 || $ph > 8.5) return 'peringatan';
        return 'baik';
    }
    
    /**
     * Get turbidity status
     */
    private static function getTurbidityStatus($turbidity, $rule = null)
    {
        if (!$rule) {
            $rule = RuleSensor::first();
        }
        
        if ($rule) {
            if ($turbidity <= $rule->turbidity_danger_low || $turbidity >= $rule->turbidity_danger_high) {
                return 'bahaya';
            }
            if ($turbidity >= $rule->turbidity_min_warning && $turbidity <= $rule->turbidity_max_warning) {
                return 'peringatan';
            }
            if ($turbidity >= $rule->turbidity_min_good && $turbidity <= $rule->turbidity_max_good) {
                return 'baik';
            }
        }
        
        if ($turbidity < 10 || $turbidity > 70) return 'bahaya';
        if ($turbidity > 50) return 'peringatan';
        return 'baik';
    }
    
    /**
     * 🔥 RESET OFFSET
     */
    public static function resetCalibration($type = 'all')
    {
        $calib = self::first() ?? new self();
        
        if ($type === 'all' || $type === 'ph') {
            $calib->ph_offset = 0;
        }
        if ($type === 'all' || $type === 'turbidity') {
            $calib->turbidity_offset = 0;
        }
        
        $calib->is_calibrated = false;
        $calib->last_calibration = null;
        $calib->save();
        
        // 🔥 Update sensor_realtime
        self::updateSensorRealtime();
        
        Cache::forget('sensor_calibration_data');
        Cache::forget('sensor_realtime_display');
        
        return $calib;
    }
    
    /**
     * 🔥 GET NILAI TERKALIBRASI
     */
    public static function getCalibratedValue($rawValue, $type)
    {
        $calib = self::first();
        if (!$calib) {
            return $rawValue;
        }
        
        $offset = ($type === 'ph') ? $calib->ph_offset : $calib->turbidity_offset;
        $value = $rawValue + $offset;
        
        if ($type === 'ph') {
            return max(0, min(14, $value));
        }
        return max(0, min(1000, $value));
    }
    
    /**
     * 🔥 GET DATA KALIBRASI
     */
    public static function getCalibration()
    {
        return self::first() ?? new self();
    }
}