<?php

namespace App\Http\Controllers;

use App\Models\FeedingRecord;
use App\Models\TambakProfile;
use App\Models\SensorRealtime;
use App\Models\RuleSensor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeedingHistoryController extends Controller
{
    // API untuk kirim pakan manual
    public function manualFeed(Request $request)
    {
        try {
            Log::info('Manual feed request received:', $request->all());
            
            // Validasi input
            $validated = $request->validate([
                'target_gram' => 'required|numeric|min:0',
                'jadwal' => 'sometimes|in:pagi,siang,sore'
            ]);
            
            $pakanGram = $request->target_gram;
            $jadwal = $request->jadwal;
            
            // 🔥 AMBIL DATA DENGAN MODEL YANG SUDAH DIUSE
            $profile = TambakProfile::first();
            $sensor = SensorRealtime::first();
            $rule = RuleSensor::first();
            
            // Tentukan jadwal jika tidak dikirim
            if (!$jadwal) {
                $jam = (int)date('H');
                if ($jam >= 5 && $jam < 11) {
                    $jadwal = 'pagi';
                } elseif ($jam >= 11 && $jam < 15) {
                    $jadwal = 'siang';
                } else {
                    $jadwal = 'sore';
                }
            }
            
            // Tentukan status air
            $statusAir = 'aman';
            if ($sensor && $rule) {
                $ph = $sensor->ph ?? 7;
                $turbidity = $sensor->turbidity ?? 30;
                
                if ($ph < $rule->ph_danger_low || $ph > $rule->ph_danger_high || 
                    $turbidity < $rule->turbidity_danger_low || $turbidity > $rule->turbidity_danger_high) {
                    $statusAir = 'bahaya';
                } elseif ($ph < $rule->ph_min_good || $ph > $rule->ph_max_good || 
                          $turbidity < $rule->turbidity_min_good || $turbidity > $rule->turbidity_max_good) {
                    $statusAir = 'peringatan';
                }
            }
            
            // 🔥 SIMPAN KE DATABASE
            $record = FeedingRecord::create([
                'target_gram' => $pakanGram,
                'pakan_kg' => round($pakanGram / 1000, 2),
                'jadwal' => $jadwal,
                'waktu_pemberian' => now()->format('H:i:s'),
                'status' => 'manual',
                'keterangan' => 'Pemberian pakan manual dari dashboard',
                'ph_saat_pemberian' => $sensor->ph ?? null,
                'turbidity_saat_pemberian' => $sensor->turbidity ?? null,
                'status_air_saat_pemberian' => $statusAir,
                'tambak_profile_id' => $profile->id ?? null
            ]);
            
            Log::info('Feeding record saved:', ['id' => $record->id, 'pakan' => $record->target_gram]);
            
            return response()->json([
                'success' => true,
                'message' => '✅ Pakan berhasil dikirim: ' . $pakanGram . ' gram',
                'data' => $record
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Manual feed error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pakan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Data hari ini
    public function today()
    {
        try {
            $records = FeedingRecord::whereDate('created_at', today())
                ->orderBy('created_at', 'asc')
                ->get();
            
            $totalGram = $records->sum('target_gram');
            
            return response()->json([
                'success' => true,
                'date' => today()->format('Y-m-d'),
                'records' => $records,
                'total_gram' => $totalGram,
                'total_kg' => round($totalGram / 1000, 2),
                'jadwal_status' => [
                    'pagi' => $records->where('jadwal', 'pagi')->isNotEmpty(),
                    'siang' => $records->where('jadwal', 'siang')->isNotEmpty(),
                    'sore' => $records->where('jadwal', 'sore')->isNotEmpty(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // Data per minggu
    public function weekly(Request $request)
    {
        try {
            $week = $request->get('week', now()->week);
            $year = $request->get('year', now()->year);
            
            $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
            $endOfWeek = Carbon::now()->setISODate($year, $week)->endOfWeek();
            
            $records = FeedingRecord::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->orderBy('created_at', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'week' => $week,
                'year' => $year,
                'period' => $startOfWeek->format('d M Y') . ' - ' . $endOfWeek->format('d M Y'),
                'records' => $records,
                'total_gram' => $records->sum('target_gram'),
                'total_kg' => round($records->sum('target_gram') / 1000, 2)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // Data per bulan
    public function monthly(Request $request)
    {
        try {
            $month = $request->get('month', now()->month);
            $year = $request->get('year', now()->year);
            
            $records = FeedingRecord::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->orderBy('created_at', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'month' => $month,
                'year' => $year,
                'period' => Carbon::create($year, $month, 1)->format('F Y'),
                'records' => $records,
                'total_gram' => $records->sum('target_gram'),
                'total_kg' => round($records->sum('target_gram') / 1000, 2)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // Semua data
    public function all()
    {
        try {
            $records = FeedingRecord::orderBy('created_at', 'asc')->get();
            
            return response()->json([
                'success' => true,
                'total_records' => $records->count(),
                'total_gram' => $records->sum('target_gram'),
                'total_kg' => round($records->sum('target_gram') / 1000, 2),
                'records' => $records
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // Halaman view history
    public function index()
    {
        $records = FeedingRecord::orderBy('created_at', 'desc')->paginate(50);
        return view('dashboard.feeding_history', compact('records'));
    }
}