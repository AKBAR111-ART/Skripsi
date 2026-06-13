<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_pengingat', function (Blueprint $table) {
            // Tambah kolom pesan jika belum ada
            if (!Schema::hasColumn('jadwal_pengingat', 'pesan')) {
                $table->text('pesan')->nullable();
            }
            
            // Tambah kolom target_nomor (JSON array untuk multiple nomor WA)
            if (!Schema::hasColumn('jadwal_pengingat', 'target_nomor')) {
                $table->json('target_nomor')->nullable();
            }
            
            // Tambah kolom is_sent (status pengiriman)
            if (!Schema::hasColumn('jadwal_pengingat', 'is_sent')) {
                $table->boolean('is_sent')->default(false);
            }
            
            // Tambah kolom last_sent_at
            if (!Schema::hasColumn('jadwal_pengingat', 'last_sent_at')) {
                $table->timestamp('last_sent_at')->nullable();
            }
            
            // Tambah kolom user_id (relasi ke users)
            if (!Schema::hasColumn('jadwal_pengingat', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_pengingat', function (Blueprint $table) {
            $columns = ['pesan', 'target_nomor', 'is_sent', 'last_sent_at', 'user_id'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('jadwal_pengingat', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};