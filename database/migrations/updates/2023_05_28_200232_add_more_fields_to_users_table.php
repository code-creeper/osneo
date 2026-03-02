<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('gender');
            $table->dropColumn('avatar');
        });

        Schema::table('users', function (Blueprint $table) {

            $table->after('dob', function (Blueprint $table){
                $table->string('employee_id')->nullable();
                $table->string('health_insurance_number')->nullable();
                $table->string('ssn')->nullable();
                $table->string('birth_name')->nullable();
                $table->string('birthplace')->nullable();
                $table->enum('gender', ['male', 'female'])->default('male');
            });

            $table->string('avatar')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
