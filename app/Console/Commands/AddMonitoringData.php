<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sensor5MinAvg;
use Carbon\Carbon;

class AddMonitoringData extends Command
{
    protected $signature = 'monitoring:add-data {--hour= : Jam yang akan ditambahkan}';
    protected $description = 'Menambahkan data monitoring untuk jam tertentu';
    
    public function handle()
    {
        $hour = $this->option('hour') ? (int)$this->option('hour') : Carbon::now()->hour;
        
        if ($hour < 6 || $hour > 22) {
            $this->warn("Jam $hour di luar range (06:00 - 22:00)");
            return Command::FAILURE;
        }
        
        $today = Carbon::today();
        $added = 0;
        
        for ($minute = 0; $minute < 60; $minute += 5) {
            $timeSlot = sprintf('%02d:%02d:00', $hour, $minute);
            
            // Cek apakah sudah ada
            $exists = Sensor5MinAvg::where('date', $today)
                        ->where('time_slot', $timeSlot)
                        ->exists();
            
            if (!$exists) {
                // Simulasi data
                $ph = 7.5 + sin($hour * pi() / 12) * 0.2 + (rand(-5, 5) / 100);
                $ph = round(max(6.5, min(8.5, $ph)), 2);
                
                $turbidity = 28 + ($hour >= 10 && $hour <= 16 ? rand(3, 12) : rand(-3, 8));
                $turbidity = max(20, min(80, $turbidity));
                
                if ($ph >= 7 && $ph <= 8 && $turbidity < 30) {
                    $status = 'Normal';
                } elseif (($ph < 7 || $ph > 8) && $turbidity > 60) {
                    $status = 'Kritis';
                } else {
                    $status = 'Sedang';
                }
                
                $periodStart = $today->setHour($hour)->setMinute($minute)->setSecond(0);
                $periodEnd = $periodStart->copy()->addMinutes(5);
                
                Sensor5MinAvg::create([
                    'avg_ph' => $ph,
                    'avg_turbidity' => $turbidity,
                    'min_ph' => round($ph - 0.05, 2),
                    'max_ph' => round($ph + 0.05, 2),
                    'min_turbidity' => max(20, $turbidity - 3),
                    'max_turbidity' => min(80, $turbidity + 3),
                    'sample_count' => rand(3, 8),
                    'status' => $status,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'date' => $today,
                    'time_slot' => $timeSlot
                ]);
                $added++;
            }
        }
        
        $this->info("✅ Ditambahkan {$added} data untuk jam {$hour}:00");
        
        return Command::SUCCESS;
    }
}