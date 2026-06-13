<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_data', function (Blueprint $table) {
            $table->id();
            
            // Data sensor utama
            $table->decimal('ph', 5, 2);
            $table->decimal('turbidity', 6, 2);
            $table->enum('status', ['Normal', 'Sedang', 'Kritis'])->default('Normal');
            $table->datetime('recorded_at');
            
            // Data tambahan
            $table->decimal('suhu', 5, 2)->nullable();
            $table->decimal('salinitas', 6, 2)->nullable();
            $table->decimal('do', 5, 2)->nullable();
            $table->decimal('ammonia', 6, 3)->nullable();
            $table->decimal('nitrite', 6, 3)->nullable();
            $table->decimal('nitrate', 6, 3)->nullable();
            
            $table->enum('sumber', ['sensor', 'manual', 'api'])->default('sensor');
            $table->text('keterangan')->nullable();
            
            // 🔥 COMMENT DULU FOREIGN KEY INI
            // $table->foreignId('input_by')->nullable()->constrained('users')->nullOnDelete();
            // $table->foreignId('tambak_profile_id')->nullable()->constrained('tambak_profiles')->nullOnDelete();
            
            // Sementara pakai ini
            $table->bigInteger('input_by')->nullable();
            $table->bigInteger('tambak_profile_id')->nullable();
            
            $table->timestamps();
            
            $table->index('recorded_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_data');
    }
};