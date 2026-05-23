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
        
        // Total minggu panen = 12 minggu (3 bulan)
        $totalWeeks = 12;
        $currentMinggu = 1;
        $umurHari = 0;
        
        if ($tambak && $tambak->tanggal_mulai_budidaya) {
            $startDate = Carbon::parse($tambak->tanggal_mulai_budidaya);
            $umurHari = $startDate->diffInDays(now());
            $currentMinggu = max(1, ceil($umurHari / 7));
            // Batasi minggu sekarang tidak lebih dari total minggu
            $currentMinggu = min($currentMinggu, $totalWeeks);
        }
        
        // ========== 1. DATA PER MINGGU (12 minggu) ==========
        $weeksData = [];
        
        for ($week = 1; $week <= $totalWeeks; $week++) {
            // Hitung tanggal mulai dan akhir minggu ini
            if ($tambak && $tambak->tanggal_mulai_budidaya) {
                $startDate = Carbon::parse($tambak->tanggal_mulai_budidaya);
                $weekStart = $startDate->copy()->addWeeks($week - 1);
                $weekEnd = $weekStart->copy()->addDays(6);
            } else {
                $weekStart = Carbon::now()->addWeeks($week - 1);
                $weekEnd = $weekStart->copy()->addDays(6);
            }
            
            // Cek apakah minggu ini sudah dilewati (<= minggu sekarang)
            $isPastWeek = ($week <= $currentMinggu);
            
            if ($isPastWeek && $tambak && $tambak->tanggal_mulai_budidaya) {
                // Ambil data REAL dari database untuk minggu yang sudah dilewati
                $weekData = DB::table('sensor_5min_avg')
                    ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                    ->select(
                        DB::raw('AVG(avg_ph) as avg_ph'),
                        DB::raw('AVG(avg_turbidity) as avg_turb'),
                        DB::raw('SUM(sample_count) as total_recordings')
                    )
                    ->first();
                
                $totalFeed = DB::table('feeding_records')
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->sum('pakan_kg');
                
                $avgPh = $weekData->avg_ph ?? 7.0;
                $avgTurb = $weekData->avg_turb ?? 12;
                
                $weeksData[] = [
                    'week' => $week,
                    'period' => "Minggu $week (" . $weekStart->format('M') . ")",
                    'date_range' => $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                    'avg_ph' => round($avgPh, 1),
                    'avg_turbidity' => round($avgTurb, 0),
                    'total_feed' => round($totalFeed, 1),
                    'status' => $this->getStatusText($avgPh, $avgTurb),
                    'has_data' => true,
                    'is_current' => ($week == $currentMinggu)
                ];
            } else {
                // Tampilkan placeholder/kosong untuk minggu yang belum datang
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
        
        // ========== 2. DATA HARIAN (hanya untuk hari yang sudah dilewati) ==========
        $dailyData = [];
        
        if ($tambak && $tambak->tanggal_mulai_budidaya) {
            $startDate = Carbon::parse($tambak->tanggal_mulai_budidaya);
            $endDate = Carbon::now();
            
            // Batasi sampai hari ini saja
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                $dateStr = $date->format('Y-m-d');
                
                $dayData = DB::table('sensor_5min_avg')
                    ->where('date', $dateStr)
                    ->select(
                        DB::raw('AVG(avg_ph) as avg_ph'),
                        DB::raw('AVG(avg_turbidity) as avg_turb')
                    )
                    ->first();
                
                $totalFeed = DB::table('feeding_records')
                    ->whereDate('created_at', $dateStr)
                    ->sum('pakan_kg');
                
                $avgPh = $dayData->avg_ph ?? 7.0;
                $avgTurb = $dayData->avg_turb ?? 12;
                
                $dailyData[] = [
                    'date' => $dateStr,
                    'day_name' => $date->locale('id')->isoFormat('dddd'),
                    'avg_ph' => round($avgPh, 1),
                    'avg_turbidity' => round($avgTurb, 0),
                    'total_feed' => round($totalFeed, 2),
                    'status' => $this->getStatusText($avgPh, $avgTurb)
                ];
            }
        }
        
        // ========== 3. STATISTIK KESELURUHAN (hitung dari data real) ==========
        $totalFeedAll = DB::table('feeding_records')->sum('pakan_kg');
        $avgAllPh = DB::table('sensor_5min_avg')->avg('avg_ph');
        $avgAllTurb = DB::table('sensor_5min_avg')->avg('avg_turbidity');
        
        $overallStats = [
            'avg_ph' => round($avgAllPh ?: 7.0, 1),
            'avg_turbidity' => round($avgAllTurb ?: 12, 0),
            'total_feed_kg' => round($totalFeedAll ?: 0, 1),
            'success_rate' => 85
        ];
        
        return view('dashboard.history', [
            'weeksData' => $weeksData,
            'dailyData' => $dailyData,
            'overallStats' => $overallStats,
            'tambak' => $tambak,
            'currentMinggu' => $currentMinggu,
            'totalWeeks' => $totalWeeks,
            'umurHari' => $umurHari
        ]);
    }
    
    /**
     * Helper: Get status text based on pH and turbidity
     */
    private function getStatusText($ph, $turb)
    {
        if ($ph < 6.5 || $ph > 8.0 || $turb > 35) {
            return 'Kritis';
        } elseif ($ph < 6.8 || $ph > 7.5 || $turb > 25) {
            return 'Perhatian';
        }
        return 'Normal';
    }
    
    /**
     * Get week data (AJAX) - untuk detail per minggu
     */
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
            $dateStr = $date->format('Y-m-d');
            
            $dayData = DB::table('sensor_5min_avg')
                ->where('date', $dateStr)
                ->select(
                    DB::raw('AVG(avg_ph) as avg_ph'),
                    DB::raw('AVG(avg_turbidity) as avg_turb')
                )
                ->first();
            
            $totalFeed = DB::table('feeding_records')
                ->whereDate('created_at', $dateStr)
                ->sum('pakan_kg');
            
            $avgPh = $dayData->avg_ph ?? 7.0;
            $avgTurb = $dayData->avg_turb ?? 12;
            
            $status = 'Baik';
            if ($avgPh < 6.5 || $avgPh > 8.0 || $avgTurb > 35) {
                $status = 'Kritis';
            } elseif ($avgPh < 6.8 || $avgPh > 7.5 || $avgTurb > 25) {
                $status = 'Perhatian';
            }
            
            $dailyData[] = [
                'date' => $dateStr,
                'day_name' => $date->locale('id')->isoFormat('dddd'),
                'avg_ph' => round($avgPh, 1),
                'avg_turbidity' => round($avgTurb, 0),
                'total_feed' => round($totalFeed, 2),
                'status' => $status
            ];
        }
        
        return response()->json(['success' => true, 'daily_data' => $dailyData]);
    }
    
    /**
     * Get day detail (AJAX) - untuk modal
     */
    public function getDayDetail(Request $request)
    {
        $date = $request->date;
        
        // Data per jam
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
                    'status' => $status,
                    'status_badge' => $this->getStatusBadge($status)
                ];
            });
        
        // Data pakan
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
                    'status' => $item->status ?? 'sudah',
                    'status_text' => ($item->status == 'success' || $item->status == 'sudah') ? '✓ Sudah' : '⌛ Belum'
                ];
            });
        
        return response()->json([
            'success' => true,
            'hourly_data' => $hourlyData,
            'feeding_data' => $feedingData,
            'notes' => 'Data dari sensor setiap 5 menit'
        ]);
    }
    
    /**
     * Get hourly data for a specific date (AJAX)
     */
    public function getHourlyData(Request $request)
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
                return [
                    'hour' => sprintf('%02d:00', $item->hour),
                    'avg_ph' => round($item->avg_ph, 1),
                    'avg_turbidity' => round($item->avg_turb, 0),
                    'status' => $this->getStatusText($item->avg_ph, $item->avg_turb)
                ];
            });
        
        return response()->json(['success' => true, 'hourly_data' => $hourlyData]);
    }
    
    /**
     * Get detail per 5 minutes for a specific hour (AJAX)
     */
    public function getDetailPer5Menit(Request $request)
    {
        $date = $request->date;
        $hour = sprintf('%02d', $request->hour);
        
        $data = DB::table('sensor_5min_avg')
            ->select('time_slot', 'avg_ph as ph', 'avg_turbidity as turbidity', 'status')
            ->where('date', $date)
            ->where('time_slot', 'like', $hour . ':%')
            ->orderBy('time_slot', 'asc')
            ->get()
            ->map(function($item) {
                return [
                    'time' => $item->time_slot,
                    'ph' => round($item->ph, 1),
                    'turbidity' => round($item->turbidity, 0),
                    'status' => $item->status
                ];
            });
        
        return response()->json(['success' => true, 'data' => $data]);
    }
    
    private function getStatusBadge($status)
    {
        return match($status) {
            'Normal' => '<span class="badge bg-success">✅ Normal</span>',
            'Perhatian' => '<span class="badge bg-warning">⚠️ Perhatian</span>',
            'Kritis' => '<span class="badge bg-danger">🔴 Kritis</span>',
            default => '<span class="badge bg-secondary">' . $status . '</span>'
        };
    }
    
    /**
     * Export data to CSV
     */
    public function exportData(Request $request)
    {
        $data = DB::table('sensor_5min_avg')
            ->orderBy('date', 'desc')
            ->orderBy('time_slot', 'asc')
            ->get();
        
        $filename = 'history_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['Tanggal', 'Waktu', 'pH', 'Kekeruhan', 'Status']);
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->date,
                    $row->time_slot,
                    $row->avg_ph,
                    $row->avg_turbidity,
                    $row->status ?? 'Normal'
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    public function getDailyMonitoring(Request $request)
{
    $date = $request->get('date', Carbon::today()->format('Y-m-d'));
    
    // Ambil data per jam dari sensor_5min_avg
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
                'status' => $this->getStatusFromAvg($items->avg('avg_ph'), $items->avg('avg_turbidity')),
                'sample_count' => $items->sum('sample_count')
            ];
        })
        ->values();
    
    // Statistik harian
    $stats = [
        'avg_ph' => round($data->avg('avg_ph'), 2),
        'avg_turbidity' => round($data->avg('avg_turbidity'), 2),
        'total_records' => $data->sum('sample_count'),
        'status' => $this->getStatusFromAvg($data->avg('avg_ph'), $data->avg('avg_turbidity'))
    ];
    
    return response()->json([
        'success' => true,
        'data' => $data,
        'stats' => $stats,
        'date' => $date
    ]);
}

private function getStatusFromAvg($ph, $turbidity)
{
    if ($ph >= 7 && $ph <= 8 && $turbidity < 50) return 'Normal';
    if ($ph < 6.5 || $ph > 8.5 || $turbidity > 100) return 'Kritis';
    return 'Perhatian';
}


}