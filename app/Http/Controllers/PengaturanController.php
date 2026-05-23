<?php

namespace App\Http\Controllers;

use App\Models\PengaturanTambak;
use App\Models\RuleSensor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PengaturanController extends Controller
{
    public function index()
    {
        $pengaturan = PengaturanTambak::first();
        $rule = RuleSensor::first();
        
        if (!$rule) {
            $rule = RuleSensor::create([
                'ph_min_good' => 7.5,
                'ph_max_good' => 8.5,
                'ph_min_warning' => 7.0,
                'ph_max_warning' => 9.0,
                'ph_min_warning_high' => 8.6,
                'ph_max_warning_high' => 8.9,
                'ph_danger_low' => 6.5,
                'ph_danger_high' => 9.5,
                'turbidity_min_good' => 0,
                'turbidity_max_good' => 300,
                'turbidity_min_warning' => 301,
                'turbidity_max_warning' => 700,
                'turbidity_danger_low' => 0,
                'turbidity_danger_high' => 700,
            ]);
        }
        
        return view('dashboard.pengaturan', compact('pengaturan', 'rule'));
    }
    
    public function store(Request $request)
    {
        try {
            $pengaturan = PengaturanTambak::first();
            
            if (!$pengaturan) {
                $pengaturan = new PengaturanTambak();
            }
            
            if ($request->has('pengingat')) {
                $pengingat = $request->pengingat;
                $pengaturan->penjaga = json_encode($pengingat['penjaga'] ?? []);
                $pengaturan->nomor_wa = json_encode($pengingat['wa'] ?? []);
                $pengaturan->waktu = json_encode($pengingat['waktu'] ?? []);
                $pengaturan->tanggal = $pengingat['tanggal'] ?? null;
                $pengaturan->template_pesan = $pengingat['template_pesan'] ?? '';
            }
            
            $pengaturan->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan berhasil disimpan'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error saving pengaturan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * UPDATE RULE SENSOR (pH + Turbidity NTU)
     */
    public function updateRule(Request $request)
    {
        try {
            $rule = RuleSensor::first();
            
            if (!$rule) {
                $rule = new RuleSensor();
            }
            
            // ==================== pH Rules ====================
            $rule->ph_min_good = $request->ph_min_good;
            $rule->ph_max_good = $request->ph_max_good;
            $rule->ph_min_warning = $request->ph_min_warning;
            $rule->ph_max_warning = $request->ph_max_warning;
            $rule->ph_min_warning_high = $request->ph_min_warning_high;
            $rule->ph_max_warning_high = $request->ph_max_warning_high;
            $rule->ph_danger_low = $request->ph_danger_low;
            $rule->ph_danger_high = $request->ph_danger_high;
            
            // ==================== Turbidity Rules (NTU) ====================
            $rule->turbidity_min_good = $request->turbidity_min_good ?? 0;
            $rule->turbidity_max_good = $request->turbidity_max_good ?? 300;
            $rule->turbidity_min_warning = $request->turbidity_min_warning ?? 301;
            $rule->turbidity_max_warning = $request->turbidity_max_warning ?? 700;
            $rule->turbidity_danger_low = $request->turbidity_danger_low ?? 0;
            $rule->turbidity_danger_high = $request->turbidity_danger_high ?? 700;
            
            $rule->save();
            
            // Clear cache agar perubahan langsung berlaku
            Cache::forget('sensor_rule');
            
            return response()->json([
                'success' => true,
                'message' => '✅ Rule sensor berhasil disimpan'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error saving rule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan rule: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * RESET RULE SENSOR KE DEFAULT NTU
     */
    public function resetRule()
    {
        try {
            $rule = RuleSensor::first();
            
            if (!$rule) {
                $rule = new RuleSensor();
            }
            
            // Reset ke default NTU
            $rule->ph_min_good = 7.5;
            $rule->ph_max_good = 8.5;
            $rule->ph_min_warning = 7.0;
            $rule->ph_max_warning = 9.0;
            $rule->ph_min_warning_high = 8.6;
            $rule->ph_max_warning_high = 8.9;
            $rule->ph_danger_low = 6.5;
            $rule->ph_danger_high = 9.5;
            
            // Turbidity NTU (0-1000)
            $rule->turbidity_min_good = 0;
            $rule->turbidity_max_good = 300;
            $rule->turbidity_min_warning = 301;
            $rule->turbidity_max_warning = 700;
            $rule->turbidity_danger_low = 0;
            $rule->turbidity_danger_high = 700;
            
            $rule->save();
            
            Cache::forget('sensor_rule');
            
            return response()->json([
                'success' => true,
                'message' => '✅ Rule berhasil direset ke default NTU'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error reset rule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal reset: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * GET LATEST RULE (dengan clear cache)
     */
    public function getLatestRule()
    {
        // Hapus cache dulu
        Cache::forget('sensor_rule');
        
        $rule = RuleSensor::first();
        
        if (!$rule) {
            // Buat default NTU jika belum ada
            $rule = RuleSensor::create([
                'ph_min_good' => 7.5,
                'ph_max_good' => 8.5,
                'ph_min_warning' => 7.0,
                'ph_max_warning' => 9.0,
                'ph_min_warning_high' => 8.6,
                'ph_max_warning_high' => 8.9,
                'ph_danger_low' => 6.5,
                'ph_danger_high' => 9.5,
                'turbidity_min_good' => 0,
                'turbidity_max_good' => 300,
                'turbidity_min_warning' => 301,
                'turbidity_max_warning' => 700,
                'turbidity_danger_low' => 0,
                'turbidity_danger_high' => 700,
            ]);
        }
        
        return response()->json($rule);
    }
}