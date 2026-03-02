<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleDriverHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('vehicle_driver_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('vehicle_id');
            $table->foreignId('driver_id');
            $table->timestamp('taken_at')->nullable();
            $table->timestamp('handed_over_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_driver_histories');
    }
}
