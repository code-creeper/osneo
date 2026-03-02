<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->uuid('lexoffice_id')->nullable();
            $table->foreignId('ticket_id')->nullable();
            $table->foreignId('contact_id');
            $table->string('name')->nullable();
            $table->json('services')->nullable();
            $table->json('sections')->nullable();
            $table->boolean('is_offer')->default(1);
            $table->json('lexoffice_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contracts');
    }
};
