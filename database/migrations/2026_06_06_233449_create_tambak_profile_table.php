<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tambak_profile', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_tambak')->nullable();
            $table->string('lokasi')->nullable();
            $table->decimal('luas', 10, 2)->nullable();
            $table->integer('populasi')->default(0);
            $table->decimal('berat_rata', 8, 2)->default(0);
            $table->date('tebar_date')->nullable();
            $table->integer('density')->default(80);
            $table->decimal('target_panen', 10, 2)->nullable();
            $table->string('nomor_wa', 20)->nullable();
            $table->string('nama_penjaga', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tambak_profile');
    }
};