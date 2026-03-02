<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('employments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('employment_type')->nullable();
            $table->unsignedInteger('weekly_target_time')->nullable();
            $table->unsignedInteger('monthly_target_time')->nullable();
            $table->json('off_days')->nullable();
            $table->float('hourly_rate')->default(0);
            $table->date('started_on');
            $table->date('ended_on')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employments');
    }
};
