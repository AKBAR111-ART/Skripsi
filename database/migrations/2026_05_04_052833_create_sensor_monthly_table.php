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
    Schema::create('sensor_monthly', function (Blueprint $table) {
        $table->id();
        $table->double('avg_ph');
        $table->double('avg_turbidity');
        $table->string('month'); // contoh: 2026-05
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_monthly');
    }
};
