<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tambak_profile', function (Blueprint $table) {

            // padat tebar (ekor per m²)
            $table->integer('density')->default(80);

            // rata-rata berat udang (gram per ekor)
            $table->float('avg_weight')->default(1);

        });
    }

    public function down(): void
    {
        Schema::table('tambak_profile', function (Blueprint $table) {

            $table->dropColumn('density');
            $table->dropColumn('avg_weight');

        });
    }
};