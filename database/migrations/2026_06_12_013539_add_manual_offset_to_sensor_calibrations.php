<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Cek apakah tabel sensor_calibrations ada
        if (Schema::hasTable('sensor_calibrations')) {
            Schema::table('sensor_calibrations', function (Blueprint $table) {
                if (!Schema::hasColumn('sensor_calibrations', 'ph_offset')) {
                    $table->decimal('ph_offset', 5, 3)->default(0)->after('id');
                }
                if (!Schema::hasColumn('sensor_calibrations', 'turbidity_offset')) {
                    $table->decimal('turbidity_offset', 8, 2)->default(0)->after('ph_offset');
                }
                if (!Schema::hasColumn('sensor_calibrations', 'calibrated_by')) {
                    $table->string('calibrated_by')->nullable()->after('turbidity_offset');
                }
            });
        } else {
            // Buat tabel baru jika belum ada
            Schema::create('sensor_calibrations', function (Blueprint $table) {
                $table->id();
                $table->decimal('ph_offset', 5, 3)->default(0);
                $table->decimal('turbidity_offset', 8, 2)->default(0);
                $table->string('calibrated_by')->nullable();
                $table->string('noise_level')->default('rendah');
                $table->boolean('is_calibrated')->default(false);
                $table->timestamp('last_calibration')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::table('sensor_calibrations', function (Blueprint $table) {
            $table->dropColumn(['ph_offset', 'turbidity_offset', 'calibrated_by']);
        });
    }
};