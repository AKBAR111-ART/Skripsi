<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rule_sensors', function (Blueprint $table) {
            $table->id();
            
            // pH Rules
            $table->float('ph_min_good')->default(7.5);
            $table->float('ph_max_good')->default(8.5);
            $table->float('ph_min_warning')->default(7.0);
            $table->float('ph_max_warning')->default(7.4);
            $table->float('ph_danger_low')->default(6.0);
            $table->float('ph_danger_high')->default(9.0);
            
            // Turbidity Rules
            $table->float('turbidity_min_good')->default(10);
            $table->float('turbidity_max_good')->default(50);
            $table->float('turbidity_min_warning')->default(51);
            $table->float('turbidity_max_warning')->default(70);
            $table->float('turbidity_danger_low')->default(10);
            $table->float('turbidity_danger_high')->default(15);
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rule_sensors');
    }
};