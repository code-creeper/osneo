<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('lexoffice_id')->nullable();
            $table->foreignId('manager_id')->nullable(); //management company id
            $table->foreignId('billing_address_id')->nullable();
            $table->string('name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_customer')->default(0);
            $table->boolean('is_supplier')->default(0);
            $table->json('customer')->nullable();
            $table->json('supplier')->nullable();
            $table->boolean('is_company')->default(0);
            $table->string('description')->nullable();
            $table->boolean('active')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contacts');
    }
};
