<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 100)->unique()->nullable();
            $table->string('phone', 15)->unique();
            $table->string('password');
            
            // Data tambak
            $table->string('tambak_name', 100)->nullable();
            $table->string('lokasi_tambak', 255)->nullable();
            $table->integer('populasi')->default(5000);
            $table->decimal('berat_rata', 8, 2)->default(15.5);
            $table->decimal('target_panen_kg', 10, 2)->default(500);
            $table->decimal('target_size_gram', 8, 2)->default(30);
            $table->date('tebar_date')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};