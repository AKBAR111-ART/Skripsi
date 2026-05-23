<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tambak_profile', function (Blueprint $table) {
            if (!Schema::hasColumn('tambak_profile', 'populasi')) {
                $table->integer('populasi')->default(5000)->after('biomassa_udang');
            }
            if (!Schema::hasColumn('tambak_profile', 'avg_weight')) {
                $table->decimal('avg_weight', 10, 2)->default(15)->after('populasi');
            }
        });
    }

    public function down()
    {
        Schema::table('tambak_profile', function (Blueprint $table) {
            $table->dropColumn(['populasi', 'avg_weight']);
        });
    }
};