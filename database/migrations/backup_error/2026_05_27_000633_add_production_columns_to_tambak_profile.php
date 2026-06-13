<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tambak_profile', function (Blueprint $table) {
            // Kolom untuk produksi
            if (!Schema::hasColumn('tambak_profile', 'populasi_awal')) {
                $table->integer('populasi_awal')->nullable()->default(0);
            }
            if (!Schema::hasColumn('tambak_profile', 'target_panen_kg')) {
                $table->decimal('target_panen_kg', 10, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('tambak_profile', 'target_size_gram')) {
                $table->decimal('target_size_gram', 5, 2)->nullable()->default(0);
            }
            
            // Kolom untuk cuaca
            if (!Schema::hasColumn('tambak_profile', 'cuaca')) {
                $table->string('cuaca')->nullable()->default('Cerah');
            }
            if (!Schema::hasColumn('tambak_profile', 'intensitas_hujan')) {
                $table->decimal('intensitas_hujan', 5, 2)->nullable()->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('tambak_profile', function (Blueprint $table) {
            $table->dropColumn([
                'populasi_awal',
                'target_panen_kg',
                'target_size_gram',
                'cuaca',
                'intensitas_hujan'
            ]);
        });
    }
};