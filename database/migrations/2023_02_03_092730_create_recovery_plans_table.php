<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recovery_plans', function (Blueprint $table) {
            $table->id();
            $table->string('tracker');
            $table->string('frequency');
            $table->time('times', $precision = 0);
            $table->dateTime('start_date', $precision = 0);
            $table->dateTime('end_date', $precision = 0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recovery_plans');
    }
};
