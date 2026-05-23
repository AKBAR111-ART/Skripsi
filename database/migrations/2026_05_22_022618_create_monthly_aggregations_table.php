<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('monthly_aggregations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tambak_profile_id')->constrained('tambak_profile')->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            $table->date('month_start_date');
            $table->date('month_end_date');
            $table->decimal('avg_ph', 3, 1);
            $table->decimal('min_ph', 3, 1);
            $table->decimal('max_ph', 3, 1);
            $table->integer('avg_turbidity');
            $table->integer('min_turbidity');
            $table->integer('max_turbidity');
            $table->decimal('total_feed_kg', 8, 2)->default(0);
            $table->integer('total_recordings');
            $table->string('status')->default('Normal');
            $table->timestamps();
            
            $table->unique(['tambak_profile_id', 'year', 'month']);
            $table->index(['tambak_profile_id', 'year', 'month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('monthly_aggregations');
    }
};