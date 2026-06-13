<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tambak_profile', function (Blueprint $table) {
            // Kolom tipe_tambak
            if (!Schema::hasColumn('tambak_profile', 'tipe_tambak')) {
                $table->string('tipe_tambak', 50)->nullable();
            }
            
            // Kolom tanggal_dibuat
            if (!Schema::hasColumn('tambak_profile', 'tanggal_dibuat')) {
                $table->date('tanggal_dibuat')->nullable();
            }
            
            // Kolom avg_weight (berat rata-rata)
            if (!Schema::hasColumn('tambak_profile', 'avg_weight')) {
                $table->decimal('avg_weight', 8, 2)->default(0);
            }
            
            // Kolom lain yang mungkin diperlukan
            if (!Schema::hasColumn('tambak_profile', 'dokter_pendamping')) {
                $table->string('dokter_pendamping', 100)->nullable();
            }
            
            if (!Schema::hasColumn('tambak_profile', 'sumber_pakan')) {
                $table->string('sumber_pakan', 100)->nullable();
            }
            
            if (!Schema::hasColumn('tambak_profile', 'jenis_udang')) {
                $table->string('jenis_udang', 50)->nullable();
            }
            
            if (!Schema::hasColumn('tambak_profile', 'sistem_aerasi')) {
                $table->string('sistem_aerasi', 50)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tambak_profile', function (Blueprint $table) {
            $columns = [
                'tipe_tambak',
                'tanggal_dibuat',
                'avg_weight',
                'dokter_pendamping',
                'sumber_pakan',
                'jenis_udang',
                'sistem_aerasi'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('tambak_profile', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};