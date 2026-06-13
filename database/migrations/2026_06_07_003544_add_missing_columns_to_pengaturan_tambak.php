<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_tambak', function (Blueprint $table) {
            // Tambah kolom penjaga
            if (!Schema::hasColumn('pengaturan_tambak', 'penjaga')) {
                $table->json('penjaga')->nullable();
            }
            
            // Tambah kolom nomor_wa
            if (!Schema::hasColumn('pengaturan_tambak', 'nomor_wa')) {
                $table->json('nomor_wa')->nullable();
            }
            
            // Tambah kolom waktu
            if (!Schema::hasColumn('pengaturan_tambak', 'waktu')) {
                $table->json('waktu')->nullable();
            }
            
            // Tambah kolom tanggal
            if (!Schema::hasColumn('pengaturan_tambak', 'tanggal')) {
                $table->date('tanggal')->nullable();
            }
            
            // Tambah kolom template_pesan
            if (!Schema::hasColumn('pengaturan_tambak', 'template_pesan')) {
                $table->text('template_pesan')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_tambak', function (Blueprint $table) {
            $columns = ['penjaga', 'nomor_wa', 'waktu', 'tanggal', 'template_pesan'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('pengaturan_tambak', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};