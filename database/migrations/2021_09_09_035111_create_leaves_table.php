<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('created_by')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('reason_id')->nullable()->constrained('leave_reasons')->nullOnDelete();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->unsignedInteger('days')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('notes', 999)->nullable();
            $table->string('remarks', 999)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leaves');
    }
};
