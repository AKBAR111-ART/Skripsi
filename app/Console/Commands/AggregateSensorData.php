<?php
// app/Console/Commands/AggregateSensorData.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AggregateSensorData extends Command
{
    protected $signature = 'sensor:aggregate';
    protected $description = 'Aggregate sensor data to 5 minutes';

    public function handle()
    {
        $this->info('🔄 Memulai agregasi data sensor...');
        
        // Ambil data terakhir yang sudah diagregasi
        $lastAggregated = DB::table('sensor_5min_avg')->max('period_end');
        
        if ($lastAggregated) {
            $startDate = Carbon::parse($lastAggregated);
            $this->info("📅 Data terakhir: " . $startDate);
        } else {
            $startDate = Carbon::now()->subDays(1);
            $this->info("📅 Mulai dari 1 hari terakhir");
        }
        
        // Query agregasi per 5 menit
        $query = DB::table('sensors')
            ->where('created_at', '>', $startDate)
            ->select(
                DB::raw("DATE_TRUNC('minute', created_at) as period_start"),
                DB::raw("DATE_TRUNC('minute', created_at) + interval '5 minute' as period_end"),
                DB::raw("ROUND(CAST(AVG(ph) AS numeric), 2) as avg_ph"),
                DB::raw("ROUND(CAST(MIN(ph) AS numeric), 2) as min_ph"),
                DB::raw("ROUND(CAST(MAX(ph) AS numeric), 2) as max_ph"),
                DB::raw("ROUND(CAST(AVG(turbidity) AS numeric), 2) as avg_turbidity"),
                DB::raw("COUNT(*) as sample_count")
            )
            ->groupBy(DB::raw("DATE_TRUNC('minute', created_at)"))
            ->havingRaw('COUNT(*) > 0')
            ->get();
        
        $inserted = 0;
        $updated = 0;
        
        foreach ($query as $row) {
            // Cek apakah sudah ada
            $exists = DB::table('sensor_5min_avg')
                ->where('period_start', $row->period_start)
                ->exists();
            
            $data = [
                'period_start' => $row->period_start,
                'period_end' => $row->period_end,
                'avg_ph' => $row->avg_ph,
                'min_ph' => $row->min_ph,
                'max_ph' => $row->max_ph,
                'avg_turbidity' => $row->avg_turbidity,
                'sample_count' => $row->sample_count,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            if ($exists) {
                DB::table('sensor_5min_avg')
                    ->where('period_start', $row->period_start)
                    ->update($data);
                $updated++;
            } else {
                DB::table('sensor_5min_avg')->insert($data);
                $inserted++;
            }
        }
        
        $this->info("✅ Selesai! Insert: {$inserted}, Update: {$updated}");
    }
}