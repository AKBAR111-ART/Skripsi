<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengaturan_tambak', function (Blueprint $table) {
            // Tambah kolom jika belum ada
            if (!Schema::hasColumn('pengaturan_tambak', 'nama_tambak')) {
                $table->string('nama_tambak')->nullable()->after('id');
            }
            if (!Schema::hasColumn('pengaturan_tambak', 'populasi')) {
                $table->integer('populasi')->default(5000)->after('nama_tambak');
            }
            if (!Schema::hasColumn('pengaturan_tambak', 'berat_rata')) {
                $table->float('berat_rata')->default(15)->after('populasi');
            }
            if (!Schema::hasColumn('pengaturan_tambak', 'umur_minggu')) {
                $table->integer('umur_minggu')->default(1)->after('berat_rata');
            }
            if (!Schema::hasColumn('pengaturan_tambak', 'pakan_per_ekor')) {
                $table->float('pakan_per_ekor')->default(1.5)->after('umur_minggu');
            }
        });
    }

    public function down()
    {
        Schema::table('pengaturan_tambak', function (Blueprint $table) {
            $table->dropColumn([
                'nama_tambak', 'populasi', 'berat_rata', 
                'umur_minggu', 'pakan_per_ekor'
            ]);
        });
    }
};