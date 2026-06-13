<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengingat_jadwal', function (Blueprint $table) {
            // Cek apakah foreign key ada, lalu hapus
            try {
                $table->dropForeign(['pengingat_harian_id']);
            } catch (\Exception $e) {
                // Foreign key tidak ada, lanjutkan
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengingat_jadwal', function (Blueprint $table) {
            $table->foreign('pengingat_harian_id')
                  ->references('id')
                  ->on('pengingat_harian')
                  ->onDelete('cascade');
        });
    }
};