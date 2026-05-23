<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengaturan_tambak', function (Blueprint $table) {
            $table->json('penjaga')->nullable()->after('rule_engine_aktif');
            $table->json('waktu')->nullable()->after('penjaga');
            $table->date('tanggal')->nullable()->after('waktu');
            $table->text('template_pesan')->nullable()->after('tanggal');
        });
    }

    public function down()
    {
        Schema::table('pengaturan_tambak', function (Blueprint $table) {
            $table->dropColumn(['penjaga', 'waktu', 'tanggal', 'template_pesan']);
        });
    }
};