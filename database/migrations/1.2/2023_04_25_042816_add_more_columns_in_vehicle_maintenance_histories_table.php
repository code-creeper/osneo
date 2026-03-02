<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehicle_maintenance_histories', function (Blueprint $table) {
            $table->renameColumn('tyre_profile_front_left', 'front_left_tyre_profile');
            $table->renameColumn('tyre_profile_front_right', 'front_right_tyre_profile');
            $table->renameColumn('tyre_profile_back_left', 'back_left_tyre_profile');
            $table->renameColumn('tyre_profile_back_right', 'back_right_tyre_profile');
        });

        Schema::table('vehicle_maintenance_histories', function (Blueprint $table) {
            $table->after('back_right_tyre_profile', function (Blueprint $table){
                $table->date('next_maintenance_date')->nullable();
                $table->date('mot_date')->nullable();
                $table->string('warning_triangle')->nullable();
                $table->string('emission_sticker', 10)->nullable();
            });
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_maintenance_histories', function (Blueprint $table) {
            $table->renameColumn('front_left_tyre_profile', 'tyre_profile_front_left');
            $table->renameColumn('front_right_tyre_profile', 'tyre_profile_front_right');
            $table->renameColumn('back_left_tyre_profile', 'tyre_profile_back_left');
            $table->renameColumn('back_right_tyre_profile', 'tyre_profile_back_right');

            $table->dropColumn(['next_maintenance_date', 'mot_date', 'warning_triangle', 'emission_sticker']);
        });


    }
};
