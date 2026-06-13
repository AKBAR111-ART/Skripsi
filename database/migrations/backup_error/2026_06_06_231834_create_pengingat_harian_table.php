<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengingat_harian', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 100);
            $table->text('deskripsi')->nullable();
            $table->time('waktu');
            $table->string('hari', 20)->nullable();
            $table->string('nama_penjaga', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengingat_harian');
    }
};
