<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_realtime', function (Blueprint $table) {

            $table->id();

            // SENSOR DATA
            $table->float('ph')->default(0);
            $table->float('turbidity')->default(0);

            // STATUS (optional cache biar tidak hitung ulang terus)
            $table->string('ph_status')->default('normal');
            $table->string('turbidity_status')->default('normal');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_realtime');
    }
};