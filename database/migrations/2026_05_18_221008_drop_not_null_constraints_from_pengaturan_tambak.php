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
            $table->float('ph_min')->nullable()->change();
            $table->float('ph_max')->nullable()->change();
            $table->float('turbidity_max')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('pengaturan_tambak', function (Blueprint $table) {
            $table->float('ph_min')->nullable(false)->change();
            $table->float('ph_max')->nullable(false)->change();
            $table->float('turbidity_max')->nullable(false)->change();
        });
    }
};