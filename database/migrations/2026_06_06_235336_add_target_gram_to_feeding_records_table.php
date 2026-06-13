<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feeding_records', function (Blueprint $table) {
            if (!Schema::hasColumn('feeding_records', 'target_gram')) {
                $table->decimal('target_gram', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('feeding_records', 'pakan_kg')) {
                $table->decimal('pakan_kg', 10, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('feeding_records', function (Blueprint $table) {
            $table->dropColumn(['target_gram']);
        });
    }
};