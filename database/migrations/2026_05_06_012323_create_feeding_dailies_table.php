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
       Schema::create('feeding_dailies', function (Blueprint $table) {
    $table->id();
    $table->date('tanggal')->unique();

    $table->float('ph')->nullable();
    $table->float('turbidity')->nullable();
    $table->float('biomassa')->nullable();

    $table->float('rekomendasi_pagi')->nullable();
    $table->float('real_pakan_pagi')->nullable();

    $table->float('rekomendasi_siang')->nullable();
    $table->float('real_pakan_siang')->nullable();

    $table->float('rekomendasi_sore')->nullable();
    $table->float('real_pakan_sore')->nullable();

    $table->float('rekomendasi_malam')->nullable();
    $table->float('real_pakan_malam')->nullable();

    $table->float('total_rekomendasi')->nullable();
    $table->float('total_real')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feeding_dailies');
    }
};
