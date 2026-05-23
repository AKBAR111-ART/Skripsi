<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            
            // pH Rules
            $table->decimal('ph_min_good', 5, 2)->default(7.5);
            $table->decimal('ph_max_good', 5, 2)->default(8.5);
            $table->decimal('ph_min_warning', 5, 2)->default(7.0);
            $table->decimal('ph_max_warning', 5, 2)->default(7.4);
            $table->decimal('ph_danger_low', 5, 2)->default(6.0);
            $table->decimal('ph_danger_high', 5, 2)->default(9.0);
            
            // Turbidity Rules
            $table->decimal('turbidity_min_good', 8, 2)->default(10);
            $table->decimal('turbidity_max_good', 8, 2)->default(50);
            $table->decimal('turbidity_min_warning', 8, 2)->default(51);
            $table->decimal('turbidity_max_warning', 8, 2)->default(70);
            $table->decimal('turbidity_danger_low', 8, 2)->default(10);
            $table->decimal('turbidity_danger_high', 8, 2)->default(15);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};