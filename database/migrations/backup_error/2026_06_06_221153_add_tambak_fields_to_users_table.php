<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cek apakah kolom sudah ada sebelum menambahkan
            if (!Schema::hasColumn('users', 'tambak_name')) {
                $table->string('tambak_name', 100)->nullable();
            }
            if (!Schema::hasColumn('users', 'lokasi_tambak')) {
                $table->string('lokasi_tambak', 255)->nullable();
            }
            if (!Schema::hasColumn('users', 'populasi')) {
                $table->integer('populasi')->default(5000);
            }
            if (!Schema::hasColumn('users', 'berat_rata')) {
                $table->decimal('berat_rata', 8, 2)->default(15.5);
            }
            if (!Schema::hasColumn('users', 'target_panen_kg')) {
                $table->decimal('target_panen_kg', 10, 2)->default(500);
            }
            if (!Schema::hasColumn('users', 'target_size_gram')) {
                $table->decimal('target_size_gram', 8, 2)->default(30);
            }
            if (!Schema::hasColumn('users', 'tebar_date')) {
                $table->date('tebar_date')->nullable();
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 15)->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tambak_name',
                'lokasi_tambak',
                'populasi',
                'berat_rata',
                'target_panen_kg',
                'target_size_gram',
                'tebar_date',
                'phone',
                'is_active'
            ]);
        });
    }
};