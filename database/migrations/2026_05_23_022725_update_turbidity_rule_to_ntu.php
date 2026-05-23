<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update default values ke NTU
        DB::table('rule_sensors')->update([
            'turbidity_min_good' => 0,
            'turbidity_max_good' => 50,
            'turbidity_min_warning' => 51,
            'turbidity_max_warning' => 100,
            'turbidity_danger_low' => 0,
            'turbidity_danger_high' => 100,
        ]);
        
        // Update default constraints
        DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_min_good SET DEFAULT 0");
        DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_max_good SET DEFAULT 50");
        DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_min_warning SET DEFAULT 51");
        DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_max_warning SET DEFAULT 100");
        DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_danger_low SET DEFAULT 0");
        DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_danger_high SET DEFAULT 100");
    }

    public function down()
    {
        DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_min_good SET DEFAULT 10");
        DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_max_good SET DEFAULT 50");
        DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_min_warning SET DEFAULT 51");
        DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_max_warning SET DEFAULT 70");
        DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_danger_low SET DEFAULT 10");
        DB::statement("ALTER TABLE rule_sensors ALTER COLUMN turbidity_danger_high SET DEFAULT 15");
    }
};