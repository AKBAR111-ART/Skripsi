<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('sensors', function (Blueprint $table) {
        $table->index('created_at');  // Untuk query history
        $table->index('ph');          // Untuk filter pH
        $table->index('turbidity');   // Untuk filter turbidity
    });
}
};
