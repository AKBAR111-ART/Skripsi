<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TambakProfile;
use Carbon\Carbon;

class ProfileController extends Controller
{
    public function index5()
    {
        $profile = TambakProfile::first();
        
        // 🔥 Jika belum ada profile, buat default dengan nilai random untuk simulasi
        if (!$profile) {
            $profile = TambakProfile::create([
                'nama_tambak' => 'Tambak Baru',
                'lokasi' => 'Lokasi belum diisi',
                'luas' => 1000,
                'tipe_tambak' => 'Traditional',
                'populasi' => 5000,
                'populasi_awal' => 5000,
                'avg_weight' => 15,
                'target_size_gram' => 30,
                // 🔥 Nilai default sensor
                'ph_raw' => rand(65, 85) / 10, // 6.5 - 8.5
                'turbidity_raw' => rand(20, 300), // 20 - 300 NTU
                'ph_offset' => 0,
                'turbidity_offset' => 0,
            ]);
        }

        return view('dashboard.profile', compact('profile'));
    }

    public function update(Request $request)
    {
        $profile = TambakProfile::first();

        if (!$profile) {
            $profile = new TambakProfile();
        }

        $profile->fill($request->all());
        $profile->save();

        return redirect()->back()->with('success', 'Profil berhasil diupdate');
    }
    
    // 🔥 ==================== API METHODS UNTUK SENSOR & KALIBRASI ====================
    
    /**
     * API: Get realtime sensor data
     */
    public function getRealtimeData()
    {
        $profile = TambakProfile::first();
        
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found'
            ], 404);
        }
        
        // 🔥 Simulasi perubahan nilai raw secara natural
        $this->simulateNaturalFluctuation($profile);
        
        return response()->json([
            'success' => true,
            'ph' => $profile->calibrated_ph,
            'ph_raw' => $profile->ph_raw,
            'ph_offset' => $profile->ph_offset,
            'ph_status' => $profile->ph_status,
            'turbidity' => $profile->calibrated_turbidity,
            'turbidity_raw' => $profile->turbidity_raw,
            'turbidity_offset' => $profile->turbidity_offset,
            'turbidity_status' => $profile->turbidity_status,
            'last_calibration_ph' => $profile->last_calibration_ph,
            'last_calibration_turbidity' => $profile->last_calibration_turbidity,
        ]);
    }
    
    /**
     * API: Calibrate pH sensor
     */
    public function calibratePh(Request $request)
    {
        $request->validate([
            'desired_value' => 'required|numeric|min:0|max:14',
            'current_value' => 'required|numeric'
        ]);
        
        $profile = TambakProfile::first();
        
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile tidak ditemukan'
            ]);
        }
        
        // 🔥 Hitung offset baru: offset_baru = desired_value - raw_value
        $newOffset = $request->desired_value - $profile->ph_raw;
        
        // Update offset
        $profile->ph_offset = round($newOffset, 2);
        $profile->last_calibration_ph = Carbon::now();
        $profile->save();
        
        return response()->json([
            'success' => true,
            'message' => "✅ Kalibrasi pH berhasil!\nNilai baru: {$request->desired_value}\nOffset: {$profile->ph_offset}",
            'new_ph' => $profile->calibrated_ph,
            'new_offset' => $profile->ph_offset
        ]);
    }
    
    /**
     * API: Calibrate Turbidity sensor (0-1000 NTU)
     */
    public function calibrateTurbidity(Request $request)
    {
        $request->validate([
            'desired_value' => 'required|numeric|min:0|max:1000',
            'current_value' => 'required|numeric'
        ]);
        
        $profile = TambakProfile::first();
        
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile tidak ditemukan'
            ]);
        }
        
        // 🔥 Hitung offset baru: offset_baru = desired_value - raw_value
        $newOffset = $request->desired_value - $profile->turbidity_raw;
        
        // Update offset
        $profile->turbidity_offset = round($newOffset);
        $profile->last_calibration_turbidity = Carbon::now();
        $profile->save();
        
        return response()->json([
            'success' => true,
            'message' => "✅ Kalibrasi Turbidity berhasil!\nNilai baru: {$request->desired_value} NTU\nOffset: {$profile->turbidity_offset}",
            'new_turbidity' => $profile->calibrated_turbidity,
            'new_offset' => $profile->turbidity_offset
        ]);
    }
    
    /**
     * API: Reset calibration to default
     */
    public function resetCalibration(Request $request)
    {
        $request->validate([
            'type' => 'required|in:ph,turbidity,both'
        ]);
        
        $profile = TambakProfile::first();
        
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile tidak ditemukan'
            ]);
        }
        
        if ($request->type === 'ph' || $request->type === 'both') {
            $profile->ph_offset = 0;
            $profile->last_calibration_ph = null;
        }
        
        if ($request->type === 'turbidity' || $request->type === 'both') {
            $profile->turbidity_offset = 0;
            $profile->last_calibration_turbidity = null;
        }
        
        $profile->save();
        
        return response()->json([
            'success' => true,
            'message' => "✅ Reset kalibrasi {$request->type} berhasil!",
            'ph' => $profile->calibrated_ph,
            'turbidity' => $profile->calibrated_turbidity
        ]);
    }
    
    /**
     * API: Get calibration status
     */
    public function getCalibrationStatus()
    {
        $profile = TambakProfile::first();
        
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'is_calibrated' => $profile->is_ph_calibrated || $profile->is_turbidity_calibrated,
            'ph' => [
                'is_calibrated' => $profile->is_ph_calibrated,
                'raw' => $profile->ph_raw,
                'offset' => $profile->ph_offset,
                'calibrated' => $profile->calibrated_ph,
                'last_calibration' => $profile->last_calibration_ph
            ],
            'turbidity' => [
                'is_calibrated' => $profile->is_turbidity_calibrated,
                'raw' => $profile->turbidity_raw,
                'offset' => $profile->turbidity_offset,
                'calibrated' => $profile->calibrated_turbidity,
                'last_calibration' => $profile->last_calibration_turbidity
            ],
            'noise_warning' => $this->checkNoiseLevel($profile)
        ]);
    }
    
    /**
     * 🔥 Simulasi fluktuasi natural nilai sensor
     */
    private function simulateNaturalFluctuation($profile)
    {
        // Fluktuasi pH: perubahan kecil antara -0.05 sampai +0.05
        $phChange = (rand(-5, 5) / 100);
        $newPhRaw = $profile->ph_raw + $phChange;
        $profile->ph_raw = max(6.0, min(9.0, round($newPhRaw, 2)));
        
        // Fluktuasi Turbidity: perubahan antara -5 sampai +5 NTU
        $turbChange = rand(-5, 5);
        $newTurbRaw = $profile->turbidity_raw + $turbChange;
        $profile->turbidity_raw = max(0, min(1000, round($newTurbRaw)));
        
        $profile->save();
    }
    
    /**
     * 🔥 Cek level noise pada sensor
     */
    private function checkNoiseLevel($profile)
    {
        // Simulasi noise berdasarkan seberapa sering kalibrasi
        $daysSincePhCalibration = $profile->last_calibration_ph 
            ? Carbon::parse($profile->last_calibration_ph)->diffInDays(now())
            : 30;
            
        $daysSinceTurbCalibration = $profile->last_calibration_turbidity 
            ? Carbon::parse($profile->last_calibration_turbidity)->diffInDays(now())
            : 30;
        
        if ($daysSincePhCalibration > 14 || $daysSinceTurbCalibration > 14) {
            return "⚠️ Sensor belum dikalibrasi dalam 14 hari, disarankan kalibrasi ulang";
        }
        
        if ($daysSincePhCalibration > 7 || $daysSinceTurbCalibration > 7) {
            return "📢 Sensor mendekati jadwal kalibrasi (7+ hari)";
        }
        
        return null;
    }
}