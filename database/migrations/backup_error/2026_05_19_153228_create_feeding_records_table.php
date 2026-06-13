<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feeding_records', function (Blueprint $table) {
            $table->id();
            
            // Data pakan
            $table->decimal('pakan_gram', 10, 2);      // Jumlah pakan dalam gram
            $table->decimal('pakan_kg', 10, 2);        // Jumlah pakan dalam kg
            
            // Jadwal pakan (1 hari 3 kali)
            $table->enum('jadwal', ['pagi', 'siang', 'sore']);  // Pagi, Siang, Sore
            $table->time('waktu_pemberian');                     // Jam pemberian
            
            // Status dan kondisi
            $table->enum('status', ['scheduled', 'manual', 'auto'])->default('scheduled');
            $table->string('keterangan')->nullable();
            
            // Data sensor saat pemberian pakan (untuk referensi)
            $table->decimal('ph_saat_pemberian', 5, 2)->nullable();
            $table->decimal('turbidity_saat_pemberian', 8, 2)->nullable();
            $table->string('status_air_saat_pemberian')->nullable();
            
            // Foreign key ke profile (opsional)
            $table->foreignId('tambak_profile_id')->nullable()->constrained('tambak_profile')->onDelete('set null');
            
            $table->timestamps();
            
            // Index untuk query cepat
            $table->index(['created_at', 'jadwal']);
            $table->index('tambak_profile_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('feeding_records');
    }
};