<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('feeding_records', function (Blueprint $table) {
            $table->dropColumn('pakan_gram');
        });
    }

    public function down()
    {
        Schema::table('feeding_records', function (Blueprint $table) {
            $table->decimal('pakan_gram', 10, 2)->nullable();
        });
    }
};