<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
       Schema::create('pengingat_jadwal', function (Blueprint $table) {
    $table->id();

    $table->foreignId('pengingat_harian_id')
        ->constrained('pengingat_harian')
        ->onDelete('cascade');

    $table->time('jam');
    $table->string('pesan')->nullable();

    $table->boolean('is_sent')->default(false); // 🔥 penting anti spam

    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('pengingat_jadwal');
    }
};