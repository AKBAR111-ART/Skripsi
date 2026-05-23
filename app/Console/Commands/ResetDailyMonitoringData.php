<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FeedingRecord;
use App\Models\Sensor5MinAvg;
use App\Models\Sensor;
use Carbon\Carbon;

class ResetDailyMonitoringData extends Command
{
    protected $signature = 'daily:reset-monitoring-data';
    protected $description = 'Reset data monitoring dan hapus data pakan kemarin';

    public function handle()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        
        $this->info("📅 Memproses data...");
        
        // ========== 1. UPDATE DATA MONITORING (dari sensor) ==========
        $this->info("📊 1. Memproses data monitoring untuk hari ini...");
        
        // Hapus data lama sensor_5min_avg untuk hari ini
        $deleted = Sensor5MinAvg::whereDate('date', $today)->delete();
        $this->info("   🗑️ Hapus data lama sensor_5min_avg: {$deleted} records");
        
        // Ambil data dari tabel sensors untuk hari ini
        $sensorData = Sensor::whereDate('created_at', $today)
                        ->orderBy('created_at', 'asc')
                        ->get();
        
        if ($sensorData->isEmpty()) {
            $this->warn("   ⚠️ Tidak ada data sensor untuk hari ini, generate data dummy...");
            $this->generateDummySensorData($today);
            $sensorData = Sensor::whereDate('created_at', $today)->orderBy('created_at', 'asc')->get();
        }
        
        $this->info("   📈 Total data sensors: " . $sensorData->count() . " records");
        
        // Kelompokkan per 5 menit
        $grouped = $sensorData->groupBy(function($item) {
            $minutes = floor($item->created_at->format('i') / 5) * 5;
            return $item->created_at->format('H') . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
        });
        
        $savedCount = 0;
        foreach ($grouped as $timeSlot => $group) {
            $avgPh = $group->avg('ph');
            $avgTurbidity = $group->avg('turbidity');
            $minPh = $group->min('ph');
            $maxPh = $group->max('ph');
            $minTurbidity = $group->min('turbidity');
            $maxTurbidity = $group->max('turbidity');
            
            $hour = (int)substr($timeSlot, 0, 2);
            $minute = (int)substr($timeSlot, 3, 2);
            $periodStart = $today->copy()->setHour($hour)->setMinute($minute)->setSecond(0);
            $periodEnd = $periodStart->copy()->addMinutes(5);
            
            $status = $this->getStatus($avgPh, $avgTurbidity);
            
            Sensor5MinAvg::create([
                'avg_ph' => round($avgPh, 2),
                'avg_turbidity' => round($avgTurbidity, 1),
                'min_ph' => round($minPh, 2),
                'max_ph' => round($maxPh, 2),
                'min_turbidity' => round($minTurbidity, 1),
                'max_turbidity' => round($maxTurbidity, 1),
                'sample_count' => $group->count(),
                'status' => $status,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'date' => $today,
                'time_slot' => $timeSlot . ':00'
            ]);
            $savedCount++;
        }
        
        $this->info("   ✅ Tersimpan {$savedCount} records ke sensor_5min_avg");
        
        // ========== 2. HAPUS DATA PAKAN KEMARIN (BUKAN MEMBUAT BARU) ==========
        $this->info("🍽️ 2. Menghapus data pakan kemarin...");
        
        // Hapus data pakan untuk KEMARIN (bukan hari ini)
        $deletedFeed = FeedingRecord::whereDate('created_at', $yesterday)->delete();
        $this->info("   🗑️ Hapus data pakan untuk tanggal {$yesterday->format('Y-m-d')}: {$deletedFeed} records");
        
        // Data hari ini tetap dipertahankan (tidak dihapus)
        $todayCount = FeedingRecord::whereDate('created_at', $today)->count();
        $this->info("   📊 Data pakan hari ini: {$todayCount} records (tidak dihapus)");
        
        $this->newLine();
        $this->info("🎉 Selesai!");
        $this->info("   - Data monitoring hari ini: {$savedCount} records");
        $this->info("   - Data pakan kemarin dihapus: {$deletedFeed} records");
        $this->info("   - Data pakan hari ini tetap: {$todayCount} records");
        
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
    
    private function generateDummySensorData($date)
    {
        // Generate data sensor dummy untuk hari ini (setiap menit)
        for ($hour = 6; $hour <= 22; $hour++) {
            for ($minute = 0; $minute < 60; $minute++) {
                $ph = 7.5 + sin($hour * pi() / 12) * 0.2 + (rand(-3, 3) / 100);
                $ph = round(max(6.5, min(8.5, $ph)), 2);
                
                $turbidity = 28 + ($hour >= 10 && $hour <= 16 ? rand(3, 10) : rand(-3, 5));
                $turbidity = max(20, min(80, $turbidity));
                
                Sensor::create([
                    'ph' => $ph,
                    'turbidity' => $turbidity,
                    'created_at' => $date->copy()->setHour($hour)->setMinute($minute)->setSecond(0),
                    'updated_at' => now()
                ]);
            }
        }
    }
}