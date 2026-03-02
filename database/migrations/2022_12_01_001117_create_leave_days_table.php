<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('leave_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reason_id')->nullable()->constrained('leave_reasons')->nullOnDelete();
            $table->date('date')->index();
        });

        foreach (\App\Models\Leave::all() as $leave){
            $leave->createLeaveDays();
        }
    }

    public function down()
    {
        Schema::dropIfExists('leave_days');
    }
};
