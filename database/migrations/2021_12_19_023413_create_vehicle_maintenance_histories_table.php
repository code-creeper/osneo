<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleMaintenanceHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('vehicle_maintenance_histories',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('vehicle_id');
                $table->foreignId('user_id')->nullable();
                $table->unsignedDecimal('mileage')->nullable();
                $table->string('outside_condition')->nullable();
                $table->string('inside_condition')->nullable();
                $table->string('tank_level')->nullable();
                $table->string('gas_card')->nullable();
                $table->string('safety_vest')->nullable();
                $table->string('first_aid_kit')->nullable();
                $table->date('first_aid_kit_expiry')->nullable();
                $table->string('craftsman_license')->nullable();
                $table->date('craftsman_license_expiry')->nullable();
                $table->string('registration')->nullable();
                $table->string('service_booklet')->nullable();
                $table->string('front_left_tyre_profile')->nullable();
                $table->string('front_right_tyre_profile')->nullable();
                $table->string('back_left_tyre_profile')->nullable();
                $table->string('back_right_tyre_profile')->nullable();
                $table->date('next_maintenance_date')->nullable();
                $table->string('warning_triangle')->nullable();
                $table->date('mot_date')->nullable();
                $table->string('emission_sticker', 10)->nullable();
                $table->timestamps();
            });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_maintenance_histories');
    }
}
