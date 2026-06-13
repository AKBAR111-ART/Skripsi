<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambah kolom phone jika belum ada
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 15)->unique()->nullable();
            }
            
            // Tambah kolom tambak_name jika belum ada
            if (!Schema::hasColumn('users', 'tambak_name')) {
                $table->string('tambak_name', 100)->nullable();
            }
            
            // Tambah kolom lokasi_tambak jika belum ada
            if (!Schema::hasColumn('users', 'lokasi_tambak')) {
                $table->string('lokasi_tambak', 255)->nullable();
            }
            
            // Tambah kolom populasi jika belum ada
            if (!Schema::hasColumn('users', 'populasi')) {
                $table->integer('populasi')->default(5000);
            }
            
            // Tambah kolom berat_rata jika belum ada
            if (!Schema::hasColumn('users', 'berat_rata')) {
                $table->decimal('berat_rata', 8, 2)->default(15.5);
            }
            
            // Tambah kolom target_panen_kg jika belum ada
            if (!Schema::hasColumn('users', 'target_panen_kg')) {
                $table->decimal('target_panen_kg', 10, 2)->default(500);
            }
            
            // Tambah kolom target_size_gram jika belum ada
            if (!Schema::hasColumn('users', 'target_size_gram')) {
                $table->decimal('target_size_gram', 8, 2)->default(30);
            }
            
            // Tambah kolom tebar_date jika belum ada
            if (!Schema::hasColumn('users', 'tebar_date')) {
                $table->date('tebar_date')->nullable();
            }
            
            // Tambah kolom is_active jika belum ada
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['phone', 'tambak_name', 'lokasi_tambak', 'populasi', 
                        'berat_rata', 'target_panen_kg', 'target_size_gram', 
                        'tebar_date', 'is_active'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};