<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->date('date');
            $table->float('hourly_rate')->default(0);
            $table->float('target_hours')->default(0);
            $table->float('working_hours')->nullable();
            $table->json('overtimes')->nullable();
            $table->json('surcharges')->nullable();
            $table->integer('leaves_balance')->nullable();
            $table->text('information')->nullable();
            $table->text('notes')->nullable();
            $table->json('vacation')->nullable();
            $table->json('leaves')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
