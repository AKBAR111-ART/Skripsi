<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateRuleSensorToNTU extends Seeder
{
    public function run()
    {
        DB::table('rule_sensors')->update([
            // pH (biarkan seperti sudah)
            'ph_min_good' => 7.5,
            'ph_max_good' => 8.5,
            'ph_min_warning' => 7.0,
            'ph_max_warning' => 9.0,
            'ph_danger_low' => 6.0,
            'ph_danger_high' => 9.5,
            
            // 🔥 TURBIDITY dalam NTU (0-1000)
            'turbidity_min_good' => 0,
            'turbidity_max_good' => 50,
            'turbidity_min_warning' => 51,
            'turbidity_max_warning' => 100,
            'turbidity_danger_low' => 0,
            'turbidity_danger_high' => 100,
            
            'updated_at' => now()
        ]);
        
        $this->command->info('✅ Rule Sensor berhasil diupdate ke standar NTU');
    }
}