<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TambakProfile;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistoryController extends Controller
{
    public function index()
    {
        $tambak = TambakProfile::first();
        
        $totalWeeks = 12;
        $currentMinggu = 1;
        $umurHari = 0;
        
        if ($tambak && $tambak->tanggal_mulai_budidaya) {
            $startDate = Carbon::parse($tambak->tanggal_mulai_budidaya);
            $umurHari = $startDate->diffInDays(now());
            $currentMinggu = max(1, ceil($umurHari / 7));
            $currentMinggu = min($currentMinggu, $totalWeeks);
        }
        
        // ========== 1. DATA PER MINGGU (12 minggu) ==========
        $weeksData = [];
        
        for ($week = 1; $week <= $totalWeeks; $week++) {
            if ($tambak && $tambak->tanggal_mulai_budidaya) {
                $startDate = Carbon::parse($tambak->tanggal_mulai_budidaya);
                $weekStart = $startDate->copy()->addWeeks($week - 1);
                $weekEnd = $weekStart->copy()->addDays(6);
            } else {
                $weekStart = Carbon::now()->addWeeks($week - 1);
                $weekEnd = $weekStart->copy()->addDays(6);
            }
            
            $isPastWeek = ($week <= $currentMinggu);
            
            if ($isPastWeek && $tambak && $tambak->tanggal_mulai_budidaya) {
                // 🔥 AMBIL RATA-RATA pH DAN TURBIDITY PER MINGGU
                $avgPh = DB::table('sensor_5min_avg')
                    ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                    ->avg('avg_ph');
                
                $avgTurb = DB::table('sensor_5min_avg')
                    ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                    ->avg('avg_turbidity');
                
                // 🔥 AMBIL TOTAL PAKAN PER MINGGU (dalam KG)
                $totalFeed = DB::table('feeding_records')
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->sum('pakan_kg');
                
                $avgPh = round($avgPh ?: 7.0, 1);
                $avgTurb = round($avgTurb ?: 12, 0);
                $totalFeed = round($totalFeed ?: 0, 1);
                
                // Tentukan status
                $status = 'Normal';
                if ($avgPh < 6.5 || $avgPh > 8.0 || $avgTurb > 100) {
                    $status = 'Kritis';
                } elseif ($avgPh < 7.0 || $avgPh > 8.0 || $avgTurb > 50) {
                    $status = 'Perhatian';
                }
                
                $weeksData[] = [
                    'week' => $week,
                    'period' => "Minggu $week (" . $weekStart->format('M') . ")",
                    'date_range' => $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                    'avg_ph' => $avgPh,
                    'avg_turbidity' => $avgTurb,
                    'total_feed' => $totalFeed,
                    'status' => $status,
                    'has_data' => true,
                    'is_current' => ($week == $currentMinggu)
                ];
            } else {
                $weeksData[] = [
                    'week' => $week,
                    'period' => "Minggu $week (" . $weekStart->format('M') . ")",
                    'date_range' => $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                    'avg_ph' => '--',
                    'avg_turbidity' => '--',
                    'total_feed' => '--',
                    'status' => 'Mendatang',
                    'has_data' => false,
                    'is_current' => false
                ];
            }
        }
        
        // ========== 2. DATA HARIAN UNTUK MINGGU AKTIF ==========
        $dailyDataForCurrentWeek = [];
        
        if ($tambak && $tambak->tanggal_mulai_budidaya) {
            $startDate = Carbon::parse($tambak->tanggal_mulai_budidaya);
            $weekStart = $startDate->copy()->addWeeks($currentMinggu - 1);
            $weekEnd = $weekStart->copy()->addDays(6);
            
            for ($date = $weekStart->copy(); $date <= $weekEnd; $date->addDay()) {
                if ($date > Carbon::now()) break;
                
                $dateStr = $date->format('Y-m-d');
                
                $avgPh = DB::table('sensor_5min_avg')
                    ->where('date', $dateStr)
                    ->avg('avg_ph');
                
                $avgTurb = DB::table('sensor_5min_avg')
                    ->where('date', $dateStr)
                    ->avg('avg_turbidity');
                
                $totalFeed = DB::table('feeding_records')
                    ->whereDate('created_at', $dateStr)
                    ->sum('pakan_kg');
                
                $dailyDataForCurrentWeek[] = [
                    'date' => $dateStr,
                    'day_name' => $date->locale('id')->isoFormat('dddd'),
                    'avg_ph' => round($avgPh ?: 7.0, 1),
                    'avg_turbidity' => round($avgTurb ?: 12, 0),
                    'total_feed' => round($totalFeed ?: 0, 2),
                    'status' => $this->getStatusText($avgPh ?: 7.0, $avgTurb ?: 12)
                ];
            }
        }
        
        // ========== 3. HITUNG BIOMASSA ==========
        $populasi = $tambak->populasi ?? 0;
        $avgWeight = $tambak->avg_weight ?? 0;
        $biomassaKg = ($populasi * $avgWeight) / 1000;
        
        // ========== 4. STATISTIK KESELURUHAN ==========
        $totalFeedAll = DB::table('feeding_records')->sum('pakan_kg');
        $avgAllPh = DB::table('sensor_5min_avg')->avg('avg_ph');
        $avgAllTurb = DB::table('sensor_5min_avg')->avg('avg_turbidity');
        
        $overallStats = [
            'avg_ph' => round($avgAllPh ?: 7.0, 1),
            'avg_turbidity' => round($avgAllTurb ?: 12, 0),
            'total_feed_kg' => round($totalFeedAll ?: 0, 1),
            'biomassa_kg' => round($biomassaKg, 2)
        ];
        
        return view('dashboard.history', [
            'weeksData' => $weeksData,
            'dailyData' => $dailyDataForCurrentWeek,
            'overallStats' => $overallStats,
            'tambak' => $tambak,
            'currentMinggu' => $currentMinggu,
            'totalWeeks' => $totalWeeks,
            'umurHari' => $umurHari,
            'biomassaKg' => $biomassaKg
        ]);
    }
    
    private function getStatusText($ph, $turb)
    {
        if ($ph < 6.5 || $ph > 8.0 || $turb > 100) {
            return 'Kritis';
        } elseif ($ph < 7.0 || $ph > 8.0 || $turb > 50) {
            return 'Perhatian';
        }
        return 'Normal';
    }
    
    public function getWeekData(Request $request)
    {
        $week = $request->week;
        $tambak = TambakProfile::first();
        
        if (!$tambak || !$tambak->tanggal_mulai_budidaya) {
            return response()->json(['success' => false, 'daily_data' => []]);
        }
        
        $startDate = Carbon::parse($tambak->tanggal_mulai_budidaya);
        $weekStart = $startDate->copy()->addWeeks($week - 1);
        $weekEnd = $weekStart->copy()->addDays(6);
        
        $dailyData = [];
        for ($date = $weekStart->copy(); $date <= $weekEnd; $date->addDay()) {
            if ($date > Carbon::now()) break;
            
            $dateStr = $date->format('Y-m-d');
            
            $avgPh = DB::table('sensor_5min_avg')
                ->where('date', $dateStr)
                ->avg('avg_ph');
            
            $avgTurb = DB::table('sensor_5min_avg')
                ->where('date', $dateStr)
                ->avg('avg_turbidity');
            
            $totalFeed = DB::table('feeding_records')
                ->whereDate('created_at', $dateStr)
                ->sum('pakan_kg');
            
            $dailyData[] = [
                'date' => $dateStr,
                'day_name' => $date->locale('id')->isoFormat('dddd'),
                'avg_ph' => round($avgPh ?: 7.0, 1),
                'avg_turbidity' => round($avgTurb ?: 12, 0),
                'total_feed' => round($totalFeed ?: 0, 2),
                'status' => $this->getStatusText($avgPh ?: 7.0, $avgTurb ?: 12)
            ];
        }
        
        // 🔥 KIRIM JUGA TOTAL PAKAN MINGGUAN UNTUK UPDATE CARD
        $totalFeedWeek = DB::table('feeding_records')
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->sum('pakan_kg');
        
        return response()->json([
            'success' => true,
            'daily_data' => $dailyData,
            'total_feed_week' => round($totalFeedWeek, 1)
        ]);
    }
    
    public function getDayDetail(Request $request)
    {
        $date = $request->date;
        
        $hourlyData = DB::table('sensor_5min_avg')
            ->select(
                DB::raw('EXTRACT(HOUR FROM time_slot::time) as hour'),
                DB::raw('AVG(avg_ph) as avg_ph'),
                DB::raw('AVG(avg_turbidity) as avg_turb')
            )
            ->where('date', $date)
            ->groupBy(DB::raw('EXTRACT(HOUR FROM time_slot::time)'))
            ->orderBy('hour', 'asc')
            ->get()
            ->map(function($item) {
                $status = $this->getStatusText($item->avg_ph, $item->avg_turb);
                return [
                    'time' => sprintf('%02d:00', $item->hour),
                    'ph' => round($item->avg_ph, 1),
                    'turbidity' => round($item->avg_turb, 0),
                    'status' => $status
                ];
            });
        
        $feedingData = DB::table('feeding_records')
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($item) {
                return [
                    'time' => Carbon::parse($item->created_at)->format('H:i'),
                    'amount' => $item->target_gram ?? ($item->pakan_kg * 1000),
                    'amount_kg' => $item->pakan_kg,
                    'note' => $item->keterangan ?? '-',
                    'status_text' => ($item->status == 'success' || $item->status == 'sudah') ? '✓ Sudah' : '⌛ Belum'
                ];
            });
        
        return response()->json([
            'success' => true,
            'hourly_data' => $hourlyData,
            'feeding_data' => $feedingData
        ]);
    }
    
    public function getDailyMonitoring(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        
        $data = DB::table('sensor_5min_avg')
            ->whereDate('date', $date)
            ->orderBy('time_slot', 'asc')
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->time_slot)->format('H');
            })
            ->map(function($items, $hour) {
                return [
                    'hour' => $hour . ':00',
                    'avg_ph' => round($items->avg('avg_ph'), 2),
                    'avg_turbidity' => round($items->avg('avg_turbidity'), 2),
                    'status' => $this->getStatusText($items->avg('avg_ph'), $items->avg('avg_turbidity')),
                    'sample_count' => $items->sum('sample_count')
                ];
            })
            ->values();
        
        $stats = [
            'avg_ph' => round($data->avg('avg_ph'), 2),
            'avg_turbidity' => round($data->avg('avg_turbidity'), 2),
            'total_records' => $data->sum('sample_count'),
            'status' => $this->getStatusText($data->avg('avg_ph'), $data->avg('avg_turbidity'))
        ];
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'stats' => $stats,
            'date' => $date
        ]);
    }
}