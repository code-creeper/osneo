<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('role_id')->nullable();
            $table->string('name')->index();
            $table->longText('value');
            $table->timestamps();


            $table->unique(['name', 'user_id']);
            $table->unique(['name', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preferences');
    }
};
