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
            $table->decimal('pakan_kg', 10, 2);
            $table->decimal('target_gram', 10, 2)->default(0);
            $table->date('tanggal')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('feeding_records');
    }
};