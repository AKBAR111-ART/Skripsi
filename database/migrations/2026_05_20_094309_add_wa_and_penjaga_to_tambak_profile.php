<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tambak_profile', function (Blueprint $table) {
            if (!Schema::hasColumn('tambak_profile', 'nomor_wa')) {
                $table->text('nomor_wa')->nullable()->after('foto_tambak');
            }
            if (!Schema::hasColumn('tambak_profile', 'penjaga')) {
                $table->string('penjaga')->nullable()->after('nomor_wa');
            }
        });
    }

    public function down()
    {
        Schema::table('tambak_profile', function (Blueprint $table) {
            $table->dropColumn(['nomor_wa', 'penjaga']);
        });
    }
};