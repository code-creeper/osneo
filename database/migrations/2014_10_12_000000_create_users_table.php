<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->date('dob')->nullable();
            $table->string('employee_id')->nullable();
            $table->string('health_insurance_number')->nullable();
            $table->string('ssn')->nullable();
            $table->string('birth_name')->nullable();
            $table->string('birthplace')->nullable();
            $table->string('address')->nullable();
            $table->schemalessAttributes('config');
            $table->boolean('active')->default(1)->index();
            $table->date('activate_on')->nullable();
            $table->date('deactivate_on')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
