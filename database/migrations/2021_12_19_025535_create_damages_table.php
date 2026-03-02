<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDamagesTable extends Migration
{
    public function up()
    {
        Schema::create('damages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('vehicle_id');
            $table->foreignId('user_id')->nullable();
            $table->string('part')->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('status_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('damages');
    }
}
