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
    Schema::create('sensor_5mins', function (Blueprint $table) {
        $table->id();
        $table->float('avg_ph')->nullable();
        $table->float('avg_turbidity')->nullable();
        $table->timestamp('time_5min')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_5mins');
    }
};
