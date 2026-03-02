<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('unit')->nullable();
            $table->json('sizes')->nullable();
            $table->longText('description')->nullable();
            $table->json('translations')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
};
