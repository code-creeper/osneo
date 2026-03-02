<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->unsignedInteger('target_time')->default(0);
            $table->unsignedInteger('working_time')->default(0);
            $table->unsignedInteger('paid_time')->default(0)->comment('Paid leaves time');
            $table->unsignedInteger('manual_time')->default(0);
            $table->integer('payout_time')->default(0)->comment('Paid overtime');
            $table->integer('overtime')->default(0);
            $table->boolean('leave')->default(0);
            $table->boolean('off_day')->default(0);
            $table->boolean('holiday')->default(0);
            $table->boolean('weekend')->default(0);

            $table->unique(['date', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_summaries');
    }
};
