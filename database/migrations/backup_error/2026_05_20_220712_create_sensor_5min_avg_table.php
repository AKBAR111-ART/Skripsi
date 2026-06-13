<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_5min_avg', function (Blueprint $table) {
            $table->id();
            
            // Nilai rata-rata per 5 menit
            $table->decimal('avg_ph', 5, 2);
            $table->decimal('avg_turbidity', 6, 2);
            $table->decimal('min_ph', 5, 2);
            $table->decimal('max_ph', 5, 2);
            $table->decimal('min_turbidity', 6, 2);
            $table->decimal('max_turbidity', 6, 2);
            $table->integer('sample_count')->default(0);
            
            // Status berdasarkan rata-rata
            $table->enum('status', ['Normal', 'Sedang', 'Kritis'])->default('Normal');
            
            // Waktu periode 5 menit
            $table->datetime('period_start');
            $table->datetime('period_end');
            $table->date('date');
            $table->time('time_slot');
            
            $table->timestamps();
            
            // Index untuk performa query
            $table->index('period_start');
            $table->index('date');
            $table->index('time_slot');
            $table->index(['date', 'time_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_5min_avg');
    }
};