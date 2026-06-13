<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeding_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tambak_profile_id')->nullable()->constrained('tambak_profile')->onDelete('set null');
            $table->decimal('pakan_kg', 10, 2)->default(0);
            $table->string('shift')->nullable(); // pagi, siang, sore
            $table->text('keterangan')->nullable();
            $table->timestamp('feeding_time')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeding_records');
    }
};