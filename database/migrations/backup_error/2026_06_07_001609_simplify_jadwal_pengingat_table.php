<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_pengingat', function (Blueprint $table) {
            // Hapus foreign key user_id jika ada
            try {
                $table->dropForeign(['user_id']);
            } catch (\Exception $e) {}
            
            // Hapus kolom yang tidak diperlukan
            $columnsToDrop = ['user_id', 'judul', 'deskripsi', 'hari', 'is_active', 'whatsapp_notif', 'last_sent_at'];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('jadwal_pengingat', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            // Pastikan kolom yang diperlukan ada
            if (!Schema::hasColumn('jadwal_pengingat', 'jam')) {
                $table->time('jam')->nullable();
            }
            
            if (!Schema::hasColumn('jadwal_pengingat', 'pesan')) {
                $table->text('pesan')->nullable();
            }
            
            if (!Schema::hasColumn('jadwal_pengingat', 'target_nomor')) {
                $table->json('target_nomor')->nullable();
            }
            
            if (!Schema::hasColumn('jadwal_pengingat', 'is_sent')) {
                $table->boolean('is_sent')->default(false);
            }
        });
    }

    public function down(): void
    {
        // Rollback tidak diperlukan untuk penyederhanaan
    }
};