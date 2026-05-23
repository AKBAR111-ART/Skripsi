<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Sensor;
use App\Models\Sensor5MinAvg;
use Carbon\Carbon;

class UpdateRealtimeMonitoring implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;
    
    public function handle()
    {
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();
        
        // Tentukan slot 5 menit saat ini
        $minutes = floor($now->minute / 5) * 5;
        $timeSlot = $now->format('H') . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':00';
        
        $periodStart = $now->copy()->setMinute($minutes)->setSecond(0);
        $periodEnd = $periodStart->copy()->addMinutes(5);
        
        // Ambil data sensor untuk 5 menit terakhir
        $sensorData = Sensor::whereBetween('created_at', [$periodStart, $periodEnd])->get();
        
        if ($sensorData->isEmpty()) {
            return;
        }
        
        // Hitung rata-rata
        $avgPh = $sensorData->avg('ph');
        $avgTurbidity = $sensorData->avg('turbidity');
        $minPh = $sensorData->min('ph');
        $maxPh = $sensorData->max('ph');
        $minTurbidity = $sensorData->min('turbidity');
        $maxTurbidity = $sensorData->max('turbidity');
        
        $phNormal = ($avgPh >= 7 && $avgPh <= 8);
        $turbNormal = ($avgTurbidity < 30);
        
        if ($phNormal && $turbNormal) {
            $status = 'Normal';
        } elseif (!$phNormal && !$turbNormal) {
            $status = 'Kritis';
        } else {
            $status = 'Sedang';
        }
        
        // Update atau create
        Sensor5MinAvg::updateOrCreate(
            [
                'date' => $today->toDateString(),
                'time_slot' => $timeSlot
            ],
            [
                'avg_ph' => round($avgPh, 2),
                'avg_turbidity' => round($avgTurbidity, 1),
                'min_ph' => round($minPh, 2),
                'max_ph' => round($maxPh, 2),
                'min_turbidity' => round($minTurbidity, 1),
                'max_turbidity' => round($maxTurbidity, 1),
                'sample_count' => $sensorData->count(),
                'status' => $status,
                'period_start' => $periodStart,
                'period_end' => $periodEnd
            ]
        );
    }
}