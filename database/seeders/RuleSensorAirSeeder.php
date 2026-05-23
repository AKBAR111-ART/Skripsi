<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RuleSensorAir;

class RuleSensorAirSeeder extends Seeder
{
    public function run(): void
    {
        RuleSensorAir::create([
            // PH
            'ph_min_good' => 7.5,
            'ph_max_good' => 8.5,
            'ph_min_warning' => 6.0,
            'ph_max_warning' => 9.0,
            'ph_danger_low' => 5.5,
            'ph_danger_high' => 9.5,

            // TURBIDITY
            'turbidity_min_good' => 0,
            'turbidity_max_good' => 10,
            'turbidity_min_warning' => 10,
            'turbidity_max_warning' => 15,
            'turbidity_danger_low' => 15,
            'turbidity_danger_high' => 100,

            'status' => true,
        ]);
    }
}