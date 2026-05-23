<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengaturan_tambak', function (Blueprint $table) {
            // Ubah kolom menjadi nullable
            $table->string('nomor_wa')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('pengaturan_tambak', function (Blueprint $table) {
            $table->string('nomor_wa')->nullable(false)->change();
        });
    }
};