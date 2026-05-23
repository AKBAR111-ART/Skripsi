<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tambak_profiles', function (Blueprint $table) {
            $table->id();
            
            // Informasi dasar
            $table->string('nama_tambak')->nullable();
            $table->string('lokasi')->nullable();
            $table->decimal('luas', 10, 2)->nullable();
            $table->string('tipe_tambak')->nullable();
            $table->date('tanggal_dibuat')->nullable();
            
            // Data budidaya
            $table->date('tanggal_mulai_budidaya')->nullable();
            $table->integer('populasi')->default(0);
            $table->decimal('avg_weight', 8, 2)->default(0);
            
            // Foto dan kontak
            $table->string('foto_tambak')->nullable();
            $table->string('wa')->nullable();
            $table->string('penjaga')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tambak_profiles');
    }
};