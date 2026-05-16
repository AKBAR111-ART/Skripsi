<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
    if (!Schema::hasTable('pengaturan_tambak')) {
    Schema::create('pengaturan_tambak', function (Blueprint $table) {
    $table->id();

    $table->float('ph_min');
    $table->float('ph_max');
    $table->float('turbidity_max');

    $table->string('nomor_wa');
    $table->boolean('whatsapp_aktif')->default(true);
    $table->boolean('rule_engine_aktif')->default(true);

    $table->timestamps();
});
}
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_tambak');
    }
};