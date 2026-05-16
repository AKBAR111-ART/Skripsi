<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
    if (!Schema::hasTable('pengingat_harian')) {
    Schema::create('pengingat_harian', function (Blueprint $table) {
    $table->id();

    $table->string('nama_penjaga'); // 🔥 TAMBAHAN BARU
    $table->date('tanggal');
    $table->string('nomor_wa');

    $table->timestamps();
});
    }
}
    public function down(): void
    {
        Schema::dropIfExists('pengingat_harian');
    }
};