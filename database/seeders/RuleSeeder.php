<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rule;

class RuleSeeder extends Seeder
{
    public function run(): void
    {
        Rule::create([
            'ph_min_good' => 7.5,
            'ph_max_good' => 8.5,
            'ph_min_warning' => 7.0,
            'ph_max_warning' => 7.4,
            'ph_danger_low' => 6.0,
            'ph_danger_high' => 9.0,
            'turbidity_min_good' => 10,
            'turbidity_max_good' => 50,
            'turbidity_min_warning' => 51,
            'turbidity_max_warning' => 70,
            'turbidity_danger_low' => 10,
            'turbidity_danger_high' => 15,
        ]);
    }
}