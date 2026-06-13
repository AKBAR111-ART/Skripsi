<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            $table->float('ph');
            $table->integer('turbidity');
            $table->string('ph_status');
            $table->string('turbidity_status');
            $table->string('overall_status'); // AMAN, PERINGATAN, BAHAYA
            $table->string('api_key');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_data');
    }
};