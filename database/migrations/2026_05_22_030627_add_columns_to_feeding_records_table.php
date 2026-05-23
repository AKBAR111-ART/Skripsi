<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('feeding_records', function (Blueprint $table) {
            // Cek dan tambah kolom jika belum ada
            if (!Schema::hasColumn('feeding_records', 'jadwal')) {
                $table->string('jadwal')->nullable()->after('target_gram');
            }
            
            if (!Schema::hasColumn('feeding_records', 'sumber')) {
                $table->string('sumber')->nullable()->after('jadwal');
            }
            
            if (!Schema::hasColumn('feeding_records', 'status')) {
                $table->string('status')->nullable()->after('sumber');
            }
            
            if (!Schema::hasColumn('feeding_records', 'waktu_pemberian')) {
                $table->time('waktu_pemberian')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('feeding_records', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('waktu_pemberian');
            }
        });
    }

    public function down()
    {
        Schema::table('feeding_records', function (Blueprint $table) {
            $table->dropColumn([
                'jadwal', 
                'sumber', 
                'status', 
                'waktu_pemberian', 
                'keterangan'
            ]);
        });
    }
};