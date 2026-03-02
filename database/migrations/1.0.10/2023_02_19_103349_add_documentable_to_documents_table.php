<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->after('id', function ($table) {
                $table->string('documentable_type')->nullable();
                $table->foreignId('documentable_id')->nullable();
            });
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('documentable_type');
            $table->dropColumn('documentable_id');
        });
    }
};
