<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Sensor;
use App\Models\Sensor5MinAvg;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Tambahkan ini

class UpdateMonitoringData implements ShouldQueue
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
        
        // Cek apakah sudah ada data untuk slot ini
        $existing = Sensor5MinAvg::where('date', $today->toDateString())
                    ->where('time_slot', $timeSlot)
                    ->exists();
        
        if ($existing) {
            return; // Sudah ada, skip
        }
        
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
        
        // Tentukan status
        $phNormal = ($avgPh >= 7 && $avgPh <= 8);
        $turbNormal = ($avgTurbidity < 30);
        
        if ($phNormal && $turbNormal) {
            $status = 'Normal';
        } elseif (!$phNormal && !$turbNormal) {
            $status = 'Kritis';
        } else {
            $status = 'Sedang';
        }
        
        // Simpan ke sensor_5min_avg
        Sensor5MinAvg::create([
            'avg_ph' => round($avgPh, 2),
            'avg_turbidity' => round($avgTurbidity, 1),
            'min_ph' => round($minPh, 2),
            'max_ph' => round($maxPh, 2),
            'min_turbidity' => round($minTurbidity, 1),
            'max_turbidity' => round($maxTurbidity, 1),
            'sample_count' => $sensorData->count(),
            'status' => $status,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'date' => $today,
            'time_slot' => $timeSlot
        ]);
        
        // Gunakan Log facade yang sudah di-import
        Log::info("Data monitoring saved for slot: {$timeSlot}");
    }
}