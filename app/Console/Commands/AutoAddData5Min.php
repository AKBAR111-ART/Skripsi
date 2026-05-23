<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sensor5MinAvg;
use Carbon\Carbon;

class AutoAddData5Min extends Command
{
    protected $signature = 'data:add-5min';
    protected $description = 'Tambah data monitoring setiap 5 menit';

    public function handle()
    {
        $now = Carbon::now();
        $hour = $now->hour;
        $minute = floor($now->minute / 5) * 5;
        
        // Hanya jam 06:00 - 23:00 (perpanjang sampai 23:00)
        if ($hour < 6 || $hour > 23) {
            $this->info("Jam $hour di luar range monitoring (06:00-23:00), skip");
            return Command::SUCCESS;
        }
        
        $timeSlot = sprintf('%02d:%02d:00', $hour, $minute);
        $today = Carbon::today();
        
        // Cek apakah sudah ada data untuk slot ini
        $exists = Sensor5MinAvg::where('date', $today)
                    ->where('time_slot', $timeSlot)
                    ->exists();
        
        if (!$exists) {
            // Generate data
            $basePh = 7.0 + ($hour / 20);
            $ph = $basePh + (rand(-8, 8) / 100);
            $ph = round(max(6.5, min(8.5, $ph)), 2);
            
            $baseTurb = 25 + ($hour / 2);
            $turbidity = $baseTurb + rand(-8, 15);
            $turbidity = round(max(20, min(80, $turbidity)));
            
            if ($ph >= 6.8 && $ph <= 8.2 && $turbidity <= 45) {
                $status = 'Normal';
            } elseif ($ph < 6.5 || $ph > 8.5 || $turbidity > 75) {
                $status = 'Kritis';
            } else {
                $status = 'Sedang';
            }
            
            $periodStart = $today->copy()->setHour($hour)->setMinute($minute);
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
            
            $this->info("✅ Data ditambahkan: {$timeSlot} | pH: {$ph} | Turb: {$turbidity} | Status: {$status}");
        } else {
            $this->info("📊 Data untuk {$timeSlot} sudah ada, skip");
        }
        
        return Command::SUCCESS;
    }
}