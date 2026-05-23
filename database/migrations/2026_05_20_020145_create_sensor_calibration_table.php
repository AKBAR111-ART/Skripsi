<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sensor_calibration', function (Blueprint $table) {
            $table->id();
            
            // Offset kalibrasi untuk pH
            $table->decimal('ph_offset', 5, 2)->default(0);
            $table->decimal('ph_default', 5, 2)->default(7.0);
            
            // Offset kalibrasi untuk Turbidity
            $table->decimal('turbidity_offset', 8, 2)->default(0);
            $table->decimal('turbidity_default', 8, 2)->default(30);
            
            // Status dan notifikasi
            $table->boolean('is_calibrated')->default(false);
            $table->timestamp('last_calibration')->nullable();
            $table->enum('noise_level', ['rendah', 'sedang', 'tinggi'])->default('rendah');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sensor_calibration');
    }
};