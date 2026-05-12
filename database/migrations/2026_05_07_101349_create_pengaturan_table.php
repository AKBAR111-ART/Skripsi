<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengaturan_tambak', function (Blueprint $table) {
            $table->id();

            // RULE SENSOR (MIN MAX JSON)
            $table->json('rule_sensor');

            // PENGINGAT
            $table->json('pengingat');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_tambak');
    }
};