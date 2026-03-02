<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('document_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_type_id')->constrained();
            $table->string('key');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('type')->nullable();
            $table->text('rules')->nullable();
            $table->unsignedInteger('order')->default(1);
            $table->boolean('is_name')->default(0);
            $table->boolean('active')->default(1);
            $table->json('translations')->nullable();

            $table->unique(['key', 'document_type_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_properties');
    }
};
