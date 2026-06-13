<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengingat_jadwal', function (Blueprint $table) {
            $table->integer('pengingat_harian_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('pengingat_jadwal', function (Blueprint $table) {
            $table->integer('pengingat_harian_id')->nullable(false)->change();
        });
    }
};