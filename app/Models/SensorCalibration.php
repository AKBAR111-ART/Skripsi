<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SensorCalibration extends Model
{
    protected $table = 'sensor_calibrations';
    
    protected $fillable = [
        'ph_offset', 'turbidity_offset', 'calibrated_by', 
        'noise_level', 'is_calibrated', 'last_calibration'
    ];
    
    /**
     * Mendapatkan data kalibrasi
     */
    public static function getCalibration()
    {
        return Cache::remember('sensor_calibration_data', 300, function () {
            $calib = DB::table('sensor_calibrations')->first();
            
            if (!$calib) {
                $id = DB::table('sensor_calibrations')->insertGetId([
                    'ph_offset' => 0,
                    'turbidity_offset' => 0,
                    'noise_level' => 'rendah',
                    'is_calibrated' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $calib = DB::table('sensor_calibrations')->find($id);
            }
            
            return $calib;
        });
    }
    
    /**
     * Mengupdate offset pH
     */
    public static function setPHOffset($offset, $calibratedBy = null)
    {
        $data = [
            'ph_offset' => $offset,
            'last_calibration' => now(),
            'is_calibrated' => true,
            'updated_at' => now()
        ];
        
        if ($calibratedBy) {
            $data['calibrated_by'] = $calibratedBy;
        }
        
        DB::table('sensor_calibrations')->updateOrInsert(
            ['id' => 1],
            $data
        );
        
        Cache::forget('sensor_calibration_data');
        Cache::forget('sensor_realtime_display');
        
        return true;
    }
    
    /**
     * Mengupdate offset turbidity
     */
    public static function setTurbidityOffset($offset, $calibratedBy = null)
    {
        $data = [
            'turbidity_offset' => $offset,
            'last_calibration' => now(),
            'is_calibrated' => true,
            'updated_at' => now()
        ];
        
        if ($calibratedBy) {
            $data['calibrated_by'] = $calibratedBy;
        }
        
        DB::table('sensor_calibrations')->updateOrInsert(
            ['id' => 1],
            $data
        );
        
        Cache::forget('sensor_calibration_data');
        Cache::forget('sensor_realtime_display');
        
        return true;
    }
    
    /**
     * Mereset kalibrasi
     */
    public static function resetCalibration($type = 'all', $calibratedBy = null)
    {
        $updateData = ['updated_at' => now()];
        
        if ($type == 'ph' || $type == 'all') {
            $updateData['ph_offset'] = 0;
        }
        
        if ($type == 'turbidity' || $type == 'all') {
            $updateData['turbidity_offset'] = 0;
        }
        
        if ($calibratedBy) {
            $updateData['calibrated_by'] = $calibratedBy;
        }
        
        DB::table('sensor_calibrations')->updateOrInsert(['id' => 1], $updateData);
        
        Cache::forget('sensor_calibration_data');
        Cache::forget('sensor_realtime_display');
        
        return true;
    }
    
    /**
     * Mendapatkan nilai yang sudah dikalibrasi
     */
    public static function getCalibratedValue($rawValue, $type)
    {
        $calibration = self::getCalibration();
        
        if ($type == 'ph') {
            $calibrated = (float)$rawValue + (float)$calibration->ph_offset;
            $calibrated = max(0, min(14, $calibrated));
        } else {
            $calibrated = (int)$rawValue + (int)$calibration->turbidity_offset;
            $calibrated = max(0, min(1000, $calibrated));
        }
        
        return $calibrated;
    }
}