<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cycle;
use App\Models\MonitoringData;
use App\Models\FeedingRecord;
use App\Models\DailySummary;
use App\Models\WeeklySummary;
use Carbon\Carbon;

class HistoryDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Mulai mengisi data...');
        
        // 1. Buat Cycle/Siklus
        $cycle = Cycle::create([
            'name' => 'Siklus Budidaya 2024',
            'start_date' => Carbon::create(2024, 1, 1),
            'end_date' => Carbon::create(2024, 3, 24),
            'total_weeks' => 12,
            'pond' => 'A',
            'status' => 'active',
            'target_harvest_kg' => 500,
            'notes' => 'Data history monitoring lengkap'
        ]);
        
        $this->command->info('Cycle berhasil dibuat, ID: ' . $cycle->id);
        
        $daysName = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        
        // 2. Generate data untuk 12 minggu
        for ($week = 1; $week <= 12; $week++) {
            $startOfWeek = Carbon::create(2024, 1, 1)->addWeeks($week - 1);
            $endOfWeek = $startOfWeek->copy()->addDays(6);
            
            // Tentukan karakteristik minggu
            $isWarningWeek = ($week >= 5 && $week <= 7);
            $isHarvestWeek = ($week >= 10);
            
            $basePh = $isWarningWeek ? 6.8 : ($isHarvestWeek ? 7.1 : 7.0);
            $baseTurbidity = $isWarningWeek ? 18 : ($isHarvestWeek ? 11 : 13);
            $weekStatus = $isWarningWeek ? 'Perhatian' : ($isHarvestWeek ? 'Panen' : 'Normal');
            
            $weeklyPhs = [];
            $weeklyTurbidities = [];
            $weeklyTotalFeed = 0;
            $weeklyFeedingCount = 0;
            
            // 3. Generate data per hari
            for ($day = 0; $day < 7; $day++) {
                $currentDate = $startOfWeek->copy()->addDays($day);
                $dayName = $daysName[$day];
                
                $dailyPhs = [];
                $dailyTurbidities = [];
                $dailyTotalFeed = 0;
                $dailyFeedingCount = 0;
                
                // Data monitoring per jam (setiap 3 jam)
                for ($hour = 0; $hour < 24; $hour += 3) {
                    $hourStr = sprintf('%02d:00', $hour);
                    
                    $phVariation = mt_rand(-30, 30) / 100;
                    $ph = round($basePh + $phVariation, 1);
                    $ph = max(6.5, min(7.8, $ph));
                    
                    $turbVariation = mt_rand(-5, 5);
                    $turbidity = $baseTurbidity + $turbVariation;
                    $turbidity = max(5, min(40, $turbidity));
                    
                    $status = 'normal';
                    if ($ph < 6.5 || $ph > 7.8 || $turbidity > 30) {
                        $status = 'warning';
                    } elseif ($ph < 6.0 || $ph > 8.5 || $turbidity > 45) {
                        $status = 'danger';
                    }
                    
                    MonitoringData::create([
                        'cycle_id' => $cycle->id,
                        'date' => $currentDate,
                        'time' => $hourStr,
                        'ph' => $ph,
                        'turbidity' => $turbidity,
                        'temperature' => 28 + mt_rand(-2, 2),
                        'salinity' => 15 + mt_rand(-3, 3),
                        'status' => $status,
                        'notes' => $isWarningWeek ? "Perhatikan kekeruhan air" : null,
                        'week' => $week,
                        'day_name' => $dayName,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    $dailyPhs[] = $ph;
                    $dailyTurbidities[] = $turbidity;
                }
                
                // Data pakan (3-5 kali sehari)
                $numFeedings = mt_rand(3, 5);
                $feedingTimes = ['06:00', '09:00', '12:00', '15:00', '18:00'];
                shuffle($feedingTimes);
                
                for ($i = 0; $i < $numFeedings; $i++) {
                    $amount = mt_rand(250, 500);
                    $status = (mt_rand(0, 1) || $week <= 8) ? 'sudah' : 'belum';
                    
                    FeedingRecord::create([
                        'cycle_id' => $cycle->id,
                        'date' => $currentDate,
                        'time' => $feedingTimes[$i],
                        'amount' => $amount,
                        'feed_type' => 'pelet',
                        'note' => 'Pemberian pakan ' . ($i + 1),
                        'status' => $status,
                        'week' => $week,
                        'given_by' => 'Admin',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    $dailyTotalFeed += $amount;
                    $dailyFeedingCount++;
                }
                
                $weeklyTotalFeed += $dailyTotalFeed;
                $weeklyFeedingCount += $dailyFeedingCount;
                
                // Hitung rata-rata harian
                $avgDailyPh = count($dailyPhs) > 0 ? round(array_sum($dailyPhs) / count($dailyPhs), 1) : 7.0;
                $avgDailyTurb = count($dailyTurbidities) > 0 ? round(array_sum($dailyTurbidities) / count($dailyTurbidities)) : 12;
                
                $dayStatus = 'Baik';
                if ($avgDailyPh < 6.8 || $avgDailyPh > 7.5 || $avgDailyTurb > 25) {
                    $dayStatus = 'Perhatian';
                }
                
                // Daily Summary
                DailySummary::create([
                    'cycle_id' => $cycle->id,
                    'date' => $currentDate,
                    'week' => $week,
                    'day_name' => $dayName,
                    'avg_ph' => $avgDailyPh,
                    'min_ph' => min($dailyPhs),
                    'max_ph' => max($dailyPhs),
                    'avg_turbidity' => $avgDailyTurb,
                    'min_turbidity' => min($dailyTurbidities),
                    'max_turbidity' => max($dailyTurbidities),
                    'total_feed' => $dailyTotalFeed,
                    'feeding_count' => $dailyFeedingCount,
                    'status' => $dayStatus,
                    'notes' => $isWarningWeek ? "Perhatikan kualitas air" : null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $weeklyPhs = array_merge($weeklyPhs, $dailyPhs);
                $weeklyTurbidities = array_merge($weeklyTurbidities, $dailyTurbidities);
            }
            
            // Hitung rata-rata mingguan
            $avgWeeklyPh = count($weeklyPhs) > 0 ? round(array_sum($weeklyPhs) / count($weeklyPhs), 1) : 7.0;
            $avgWeeklyTurb = count($weeklyTurbidities) > 0 ? round(array_sum($weeklyTurbidities) / count($weeklyTurbidities)) : 12;
            
            // Weekly Summary
            WeeklySummary::create([
                'cycle_id' => $cycle->id,
                'week' => $week,
                'week_start_date' => $startOfWeek,
                'week_end_date' => $endOfWeek,
                'period' => "Minggu $week (" . $startOfWeek->format('M') . ")",
                'avg_ph' => $avgWeeklyPh,
                'min_ph' => min($weeklyPhs),
                'max_ph' => max($weeklyPhs),
                'avg_turbidity' => $avgWeeklyTurb,
                'min_turbidity' => min($weeklyTurbidities),
                'max_turbidity' => max($weeklyTurbidities),
                'total_feed' => $weeklyTotalFeed / 1000,
                'total_feeding_count' => $weeklyFeedingCount,
                'status' => $weekStatus,
                'success_rate' => $week <= 8 ? 92 : ($week <= 10 ? 85 : 95),
                'insights' => $this->getInsight($week),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->command->info("Minggu $week selesai");
        }
        
        $this->command->info('History data seeded successfully!');
    }
    
    private function getInsight($week)
    {
        if ($week >= 5 && $week <= 7) {
            return '⚠️ Perhatikan kekeruhan air, lakukan pergantian air parsial';
        } elseif ($week >= 10) {
            return '🎯 Memasuki masa panen, monitor kualitas air dengan ketat';
        } else {
            return '✅ Kondisi optimal, lanjutkan jadwal pakan normal';
        }
    }
}