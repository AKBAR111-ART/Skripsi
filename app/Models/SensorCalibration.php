<?php
// app/Models/SensorCalibration.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorCalibration extends Model
{
    protected $table = 'sensor_calibration';
    
    protected $fillable = [
        'ph_offset', 'ph_default', 
        'turbidity_offset', 'turbidity_default',
        'is_calibrated', 'last_calibration', 'noise_level'
    ];
    
    protected $casts = [
        'is_calibrated' => 'boolean',
        'last_calibration' => 'datetime'
    ];
    
    // Ambil data kalibrasi (selalu 1 baris)
    public static function getCalibration()
    {
        $calib = self::first();
        if (!$calib) {
            $calib = self::create([
                'ph_offset' => 0,
                'ph_default' => 7.0,
                'turbidity_offset' => 0,
                'turbidity_default' => 30,
                'is_calibrated' => false,
                'noise_level' => 'rendah'
            ]);
        }
        return $calib;
    }
    
    // Hitung nilai setelah kalibrasi
    public static function getCalibratedValue($rawValue, $type)
    {
        $calib = self::getCalibration();
        
        if ($type === 'ph') {
            $calibrated = $rawValue - $calib->ph_offset;
            
            // Update noise level berdasarkan offset
            if (abs($calib->ph_offset) > 1.5) {
                $calib->update(['noise_level' => 'tinggi']);
            } elseif (abs($calib->ph_offset) > 0.5) {
                $calib->update(['noise_level' => 'sedang']);
            } else {
                $calib->update(['noise_level' => 'rendah']);
            }
            
            return round($calibrated, 2);
        }
        
        if ($type === 'turbidity') {
            $calibrated = $rawValue - $calib->turbidity_offset;
            
            // Update noise level berdasarkan offset
            if (abs($calib->turbidity_offset) > 20) {
                $calib->update(['noise_level' => 'tinggi']);
            } elseif (abs($calib->turbidity_offset) > 10) {
                $calib->update(['noise_level' => 'sedang']);
            } else {
                $calib->update(['noise_level' => 'rendah']);
            }
            
            return max(0, round($calibrated, 0));
        }
        
        return $rawValue;
    }
    
    // Reset kalibrasi (kembalikan ke nilai default)
    public static function resetCalibration($type)
    {
        $calib = self::getCalibration();
        
        if ($type === 'ph') {
            $calib->ph_offset = 0;
            $calib->ph_default = 7.0;
        } elseif ($type === 'turbidity') {
            $calib->turbidity_offset = 0;
            $calib->turbidity_default = 30;
        }
        
        $calib->is_calibrated = false;
        $calib->last_calibration = now();
        $calib->save();
        
        return $calib;
    }
    
    // Set kalibrasi baru berdasarkan nilai yang diinginkan
    public static function setCalibration($type, $desiredValue, $currentValue)
    {
        $calib = self::getCalibration();
        
        if ($type === 'ph') {
            $calib->ph_offset = $currentValue - $desiredValue;
            $calib->ph_default = $desiredValue;
        } elseif ($type === 'turbidity') {
            $calib->turbidity_offset = $currentValue - $desiredValue;
            $calib->turbidity_default = $desiredValue;
        }
        
        $calib->is_calibrated = true;
        $calib->last_calibration = now();
        $calib->save();
        
        return $calib;
    }
}