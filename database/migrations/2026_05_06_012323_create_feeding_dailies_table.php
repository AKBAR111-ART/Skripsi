<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('feeding_dailies', function (Blueprint $table) {
    $table->id();
    $table->date('tanggal')->unique();
    $table->float('pakan_gram');
    $table->float('ph')->nullable();
    $table->float('turbidity')->nullable();
    $table->string('status')->nullable();
    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('feeding_dailies');
    }
};