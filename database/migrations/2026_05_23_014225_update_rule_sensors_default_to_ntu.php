<?php
// database/migrations/2026_05_23_000001_update_rule_sensors_default_to_ntu.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Cek apakah tabel rule_sensors ada
        if (Schema::hasTable('rule_sensors')) {
            
            // ========== 1. UBAH DEFAULT VALUE DI LEVEL DATABASE ==========
            // pH
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN ph_min_good SET DEFAULT 7.5");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN ph_max_good SET DEFAULT 8.5");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN ph_min_warning SET DEFAULT 7.0");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN ph_max_warning SET DEFAULT 9.0");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN ph_danger_low SET DEFAULT 6.0");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN ph_danger_high SET DEFAULT 9.5");
            
            // Turbidity dalam NTU (0-1000)
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_min_good SET DEFAULT 0");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_max_good SET DEFAULT 50");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_min_warning SET DEFAULT 51");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_max_warning SET DEFAULT 100");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_danger_low SET DEFAULT 0");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_danger_high SET DEFAULT 100");
            
            // ========== 2. UPDATE DATA YANG SUDAH ADA ==========
            DB::table('rule_sensors')->update([
                'ph_min_good' => 7.5,
                'ph_max_good' => 8.5,
                'ph_min_warning' => 7.0,
                'ph_max_warning' => 9.0,
                'ph_danger_low' => 6.0,
                'ph_danger_high' => 9.5,
                'turbidity_min_good' => 0,
                'turbidity_max_good' => 50,
                'turbidity_min_warning' => 51,
                'turbidity_max_warning' => 100,
                'turbidity_danger_low' => 0,
                'turbidity_danger_high' => 100,
                'updated_at' => now()
            ]);
            
            echo "✅ Rule Sensors updated to NTU standard\n";
        }
    }

    public function down()
    {
        if (Schema::hasTable('rule_sensors')) {
            // Kembalikan ke default lama
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_min_good SET DEFAULT 10");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_max_good SET DEFAULT 50");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_min_warning SET DEFAULT 51");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_max_warning SET DEFAULT 70");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_danger_low SET DEFAULT 10");
            DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_danger_high SET DEFAULT 15");
            
            echo "↩️ Rollback to old default values\n";
        }
    }
};