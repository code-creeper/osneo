<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConstantsTable extends Migration
{
    public function up()
    {
        Schema::create('constants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('group')->default('general');
            $table->string('key')->nullable();
            $table->longText('value')->nullable();
            $table->schemalessAttributes('fields');
            $table->json('translations')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('constants');
    }
}
