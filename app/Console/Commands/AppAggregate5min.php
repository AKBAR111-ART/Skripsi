<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sensor;
use App\Models\Sensor5MinAvg;
use Carbon\Carbon;

class AppAggregate5min extends Command
{
    protected $signature = 'app:aggregate5min';
    protected $description = 'Aggregate sensor data every 5 minutes';

    public function handle()
    {
        $this->info("Command aggregation 5 menit dijalankan");
        
        $end = now();
        $start = now()->subMinutes(5);
        
        // 🔥 Ambil data 5 menit terakhir
        $data = Sensor::whereBetween('created_at', [$start, $end])->get();
        
        if ($data->count() == 0) {
            $this->warn("Tidak ada data untuk periode ini");
            return Command::SUCCESS;
        }
        
        // 🔥 Hitung semua statistik
        $avgPh = $data->avg('ph');
        $avgTurbidity = $data->avg('turbidity');
        $minPh = $data->min('ph');
        $maxPh = $data->max('ph');
        $minTurbidity = $data->min('turbidity');
        $maxTurbidity = $data->max('turbidity');
        $sampleCount = $data->count();
        
        $periodStart = Carbon::parse($start)->setSecond(0);
        
        // 🔥 Tentukan status
        $status = $this->getStatus($avgPh, $avgTurbidity);
        
        // 🔥 INSERT dengan semua kolom yang required
        Sensor5MinAvg::updateOrCreate(
            [
                'date' => $periodStart->toDateString(),
                'time_slot' => $periodStart->format('H:i:s')
            ],
            [
                'avg_ph' => round($avgPh, 2),
                'avg_turbidity' => round($avgTurbidity, 2),
                'min_ph' => round($minPh, 2),
                'max_ph' => round($maxPh, 2),
                'min_turbidity' => round($minTurbidity, 2),
                'max_turbidity' => round($maxTurbidity, 2),
                'sample_count' => $sampleCount,
                'status' => $status,
                'period_start' => $periodStart,
                'period_end' => $periodStart->copy()->addMinutes(5),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        
        $this->info("✅ Data 5 menit tersimpan: pH={$avgPh}, NTU={$avgTurbidity}, Status={$status}");
        
        return Command::SUCCESS;
    }
    
    private function getStatus($avgPh, $avgTurbidity)
    {
        $phNormal = ($avgPh >= 7 && $avgPh <= 8);
        $turbNormal = ($avgTurbidity < 30);
        
        if ($phNormal && $turbNormal) return 'Normal';
        if (!$phNormal && !$turbNormal) return 'Kritis';
        return 'Sedang';
    }
}