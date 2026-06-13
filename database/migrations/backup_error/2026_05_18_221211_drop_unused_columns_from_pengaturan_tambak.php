<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengaturan_tambak', function (Blueprint $table) {
            // Hapus kolom yang tidak dipakai
            if (Schema::hasColumn('pengaturan_tambak', 'ph_min')) {
                $table->dropColumn('ph_min');
            }
            if (Schema::hasColumn('pengaturan_tambak', 'ph_max')) {
                $table->dropColumn('ph_max');
            }
            if (Schema::hasColumn('pengaturan_tambak', 'turbidity_max')) {
                $table->dropColumn('turbidity_max');
            }
        });
    }

    public function down()
    {
        Schema::table('pengaturan_tambak', function (Blueprint $table) {
            $table->float('ph_min')->nullable();
            $table->float('ph_max')->nullable();
            $table->float('turbidity_max')->nullable();
        });
    }
};