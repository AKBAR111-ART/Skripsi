<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sensor;
use Carbon\Carbon;

class GenerateSensorData extends Command
{
    protected $signature = 'sensor:generate-realtime';
    protected $description = 'Generate data sensor realtime setiap menit';
    
    public function handle()
    {
        $now = Carbon::now();
        $hour = $now->hour;
        
        // Simulasi data sensor
        $ph = 7.5 + sin($hour * pi() / 12) * 0.2 + (rand(-3, 3) / 100);
        $ph = round(max(6.5, min(8.5, $ph)), 2);
        
        $turbidity = 28 + ($hour >= 10 && $hour <= 16 ? rand(3, 10) : rand(-3, 5));
        $turbidity = max(20, min(80, $turbidity));
        
        // Tentukan status berdasarkan nilai
        $statusPh = $this->getStatusPh($ph);
        $statusTurbidity = $this->getStatusTurbidity($turbidity);
        
        Sensor::create([
            'ph' => $ph,
            'status_ph' => $statusPh,
            'turbidity' => $turbidity,
            'status_turbidity' => $statusTurbidity,
            'created_at' => $now,
            'updated_at' => $now
        ]);
        
        $this->info("✅ Data sensor generated: pH={$ph} ({$statusPh}), Turbidity={$turbidity} ({$statusTurbidity})");
        
        return Command::SUCCESS;
    }
    
    private function getStatusPh($ph)
    {
        if ($ph >= 7 && $ph <= 8) return 'Normal';
        if ($ph > 8) return 'Tinggi';
        return 'Rendah';
    }
    
    private function getStatusTurbidity($turbidity)
    {
        if ($turbidity < 30) return 'Normal';
        if ($turbidity < 60) return 'Sedang';
        return 'Tinggi';
    }
}