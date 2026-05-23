<?php
// database/seeders/RuleSensorSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RuleSensorSeeder extends Seeder
{
    public function run()
    {
        // Cek apakah sudah ada data
        $exists = DB::table('rule_sensors')->count() > 0;
        
        if (!$exists) {
            // Jika belum ada data, insert baru
            DB::table('rule_sensors')->insert([
                // pH
                'ph_min_good' => 7.5,
                'ph_max_good' => 8.5,
                'ph_min_warning' => 7.0,
                'ph_max_warning' => 9.0,
                'ph_danger_low' => 6.0,
                'ph_danger_high' => 9.5,
                'ph_min_warning_high' => 8.6,
                'ph_max_warning_high' => 8.9,
                
                // Turbidity dalam NTU (0-1000)
                'turbidity_min_good' => 0,
                'turbidity_max_good' => 50,
                'turbidity_min_warning' => 51,
                'turbidity_max_warning' => 100,
                'turbidity_danger_low' => 0,
                'turbidity_danger_high' => 100,
                
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->command->info('✅ Rule Sensors berhasil diisi (NTU standard)');
        } else {
            // Jika sudah ada data, update ke standar NTU
            DB::table('rule_sensors')->update([
                'turbidity_min_good' => 0,
                'turbidity_max_good' => 50,
                'turbidity_min_warning' => 51,
                'turbidity_max_warning' => 100,
                'turbidity_danger_low' => 0,
                'turbidity_danger_high' => 100,
                'updated_at' => now()
            ]);
            
            $this->command->info('✅ Rule Sensors sudah diupdate ke NTU standard');
        }
    }
}