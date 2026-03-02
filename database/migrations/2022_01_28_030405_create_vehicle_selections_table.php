<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleSelectionsTable extends Migration
{
    public function up()
    {
        Schema::create('vehicle_selections',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('vehicle_id')->nullable();
                $table->foreignId('user_id');
                $table->timestamp('created_at')->nullable();
            });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_selections');
    }
}
