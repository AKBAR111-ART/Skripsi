<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rule_sensors', function (Blueprint $table) {
            if (!Schema::hasColumn('rule_sensors', 'ph_min_warning_high')) {
                $table->float('ph_min_warning_high')->default(8.6)->after('ph_max_warning');
            }
            if (!Schema::hasColumn('rule_sensors', 'ph_max_warning_high')) {
                $table->float('ph_max_warning_high')->default(8.9)->after('ph_min_warning_high');
            }
        });
    }

    public function down()
    {
        Schema::table('rule_sensors', function (Blueprint $table) {
            $table->dropColumn(['ph_min_warning_high', 'ph_max_warning_high']);
        });
    }
};