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
      Schema::create('sensor_10mins', function (Blueprint $table) {
    $table->id();
    $table->float('avg_ph');
    $table->float('avg_turbidity');
    $table->timestamp('time_10min');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor10_mins');
    }
};
