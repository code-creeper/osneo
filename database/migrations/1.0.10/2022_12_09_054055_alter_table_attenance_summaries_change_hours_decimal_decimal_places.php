<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('attendance_summaries', function (Blueprint $table) {
            $table->unsignedDecimal('target_hours', 10, 8)->change();
            $table->unsignedDecimal('working_hours', 10, 8)->change();
        });
    }

    public function down()
    {
        Schema::table('attendance_summaries', function (Blueprint $table) {
            $table->unsignedDecimal('target_hours')->change();
            $table->unsignedDecimal('working_hours')->change();
        });
    }
};
