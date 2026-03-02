<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
			$table->string('color')->nullable();
			$table->boolean('paid')->default(0);
			$table->boolean('deductible')->default(1);
            $table->json('translations')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_reasons');
    }
};
