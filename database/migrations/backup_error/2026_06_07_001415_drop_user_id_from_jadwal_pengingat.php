<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_pengingat', function (Blueprint $table) {
            // Cek apakah foreign key ada, lalu hapus
            try {
                $table->dropForeign(['user_id']);
            } catch (\Exception $e) {
                // Foreign key tidak ada
            }
            
            // Hapus kolom user_id
            if (Schema::hasColumn('jadwal_pengingat', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_pengingat', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
        });
    }
};