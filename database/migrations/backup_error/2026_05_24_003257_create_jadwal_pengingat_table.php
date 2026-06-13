<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jadwal_pengingat', function (Blueprint $table) {
            $table->id();
            $table->string('jam'); // format HH:MM
            $table->text('pesan');
            $table->json('target_nomor')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jadwal_pengingat');
    }
};