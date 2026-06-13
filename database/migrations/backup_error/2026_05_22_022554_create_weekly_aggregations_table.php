<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('weekly_aggregations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tambak_profile_id')->constrained('tambak_profile')->onDelete('cascade');
            $table->integer('year');
            $table->integer('week');
            $table->date('week_start_date');
            $table->date('week_end_date');
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
            
            $table->unique(['tambak_profile_id', 'year', 'week']);
            $table->index(['tambak_profile_id', 'year', 'week']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('weekly_aggregations');
    }
};