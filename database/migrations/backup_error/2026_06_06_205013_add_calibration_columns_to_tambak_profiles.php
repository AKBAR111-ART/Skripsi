<?php
// database/migrations/2026_01_15_000001_add_calibration_columns_to_tambak_profile.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tambak_profile', function (Blueprint $table) {
            // 🔥 Kolom untuk sensor dan kalibrasi
            $table->float('ph_raw')->default(7.0)->after('intensitas_hujan');
            $table->float('ph_offset')->default(0)->after('ph_raw');
            $table->float('turbidity_raw')->default(30)->after('ph_offset');
            $table->float('turbidity_offset')->default(0)->after('turbidity_raw');
            
            // Timestamp kalibrasi terakhir
            $table->timestamp('last_calibration_ph')->nullable()->after('turbidity_offset');
            $table->timestamp('last_calibration_turbidity')->nullable()->after('last_calibration_ph');
        });
    }

    public function down()
    {
        Schema::table('tambak_profile', function (Blueprint $table) {
            $table->dropColumn([
                'ph_raw', 
                'ph_offset', 
                'turbidity_raw', 
                'turbidity_offset',
                'last_calibration_ph', 
                'last_calibration_turbidity'
            ]);
        });
    }
};