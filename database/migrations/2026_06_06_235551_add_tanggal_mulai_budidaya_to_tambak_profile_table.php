<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tambak_profile', function (Blueprint $table) {
            if (!Schema::hasColumn('tambak_profile', 'tanggal_mulai_budidaya')) {
                $table->date('tanggal_mulai_budidaya')->nullable();
            }
            if (!Schema::hasColumn('tambak_profile', 'tanggal_tebar')) {
                $table->date('tanggal_tebar')->nullable();
            }
            if (!Schema::hasColumn('tambak_profile', 'estimasi_panen')) {
                $table->date('estimasi_panen')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tambak_profile', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_mulai_budidaya',
                'tanggal_tebar',
                'estimasi_panen'
            ]);
        });
    }
};