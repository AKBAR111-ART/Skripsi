<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengingat', function (Blueprint $table) {
            $table->id();
            $table->json('penjaga')->nullable();
            $table->json('nomor_wa')->nullable();
            $table->json('waktu')->nullable();
            $table->date('tanggal')->nullable();
            $table->text('template_pesan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengingat');
    }
};