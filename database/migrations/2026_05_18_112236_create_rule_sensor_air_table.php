<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rule_sensor_air', function (Blueprint $table) {

            $table->id();

            // =========================
            // RULE pH
            // =========================
            $table->float('ph_min_good');
            $table->float('ph_max_good');

            $table->float('ph_min_warning');
            $table->float('ph_max_warning');

            $table->float('ph_danger_low');
            $table->float('ph_danger_high');

            // =========================
            // RULE TURBIDITY
            // =========================
            $table->float('turbidity_min_good');
            $table->float('turbidity_max_good');

            $table->float('turbidity_min_warning');
            $table->float('turbidity_max_warning');

            $table->float('turbidity_danger_low');
            $table->float('turbidity_danger_high');

            // ACTIVE RULE (biar bisa switch rule)
            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rule_sensor_air');
    }
};