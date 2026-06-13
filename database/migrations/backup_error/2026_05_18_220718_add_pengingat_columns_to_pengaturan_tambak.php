<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengaturan_tambak', function (Blueprint $table) {
            if (!Schema::hasColumn('pengaturan_tambak', 'penjaga')) {
                $table->json('penjaga')->nullable()->after('id');
            }
            if (!Schema::hasColumn('pengaturan_tambak', 'nomor_wa')) {
                $table->json('nomor_wa')->nullable()->after('penjaga');
            }
            if (!Schema::hasColumn('pengaturan_tambak', 'waktu')) {
                $table->json('waktu')->nullable()->after('nomor_wa');
            }
            if (!Schema::hasColumn('pengaturan_tambak', 'tanggal')) {
                $table->date('tanggal')->nullable()->after('waktu');
            }
            if (!Schema::hasColumn('pengaturan_tambak', 'template_pesan')) {
                $table->text('template_pesan')->nullable()->after('tanggal');
            }
        });
    }

    public function down()
    {
        Schema::table('pengaturan_tambak', function (Blueprint $table) {
            $table->dropColumn(['penjaga', 'nomor_wa', 'waktu', 'tanggal', 'template_pesan']);
        });
    }
};