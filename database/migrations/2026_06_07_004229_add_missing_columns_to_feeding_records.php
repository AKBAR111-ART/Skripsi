<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feeding_records', function (Blueprint $table) {
            // Kolom jadwal (pagi/siang/sore)
            if (!Schema::hasColumn('feeding_records', 'jadwal')) {
                $table->string('jadwal', 20)->nullable();
            }
            
            // Kolom status
            if (!Schema::hasColumn('feeding_records', 'status')) {
                $table->string('status', 50)->nullable();
            }
            
            // Kolom waktu_pemberian
            if (!Schema::hasColumn('feeding_records', 'waktu_pemberian')) {
                $table->time('waktu_pemberian')->nullable();
            }
            
            // Kolom keterangan
            if (!Schema::hasColumn('feeding_records', 'keterangan')) {
                $table->text('keterangan')->nullable();
            }
            
            // Kolom target_gram (jika belum ada)
            if (!Schema::hasColumn('feeding_records', 'target_gram')) {
                $table->decimal('target_gram', 10, 2)->nullable();
            }
            
            // Kolom pakan_kg (jika belum ada)
            if (!Schema::hasColumn('feeding_records', 'pakan_kg')) {
                $table->decimal('pakan_kg', 10, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('feeding_records', function (Blueprint $table) {
            $columns = ['jadwal', 'status', 'waktu_pemberian', 'keterangan'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('feeding_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};