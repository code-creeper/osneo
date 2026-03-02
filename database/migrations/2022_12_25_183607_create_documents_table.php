<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable();
            $table->string('documentable_type')->nullable();
            $table->foreignId('documentable_id')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users');
            $table->foreignId('sorted_by')->nullable()->constrained('users');
            $table->foreignId('ticket_id')->nullable()->constrained();
            $table->foreignId('document_type_id')->nullable()->constrained();
            $table->string('name')->index();
            $table->string('sorted_path')->nullable();
            $table->enum('source', ['DEB', 'KRE'])->nullable()->index();
            $table->string('lexoffice_id')->nullable();
            $table->enum('lexoffice_voucher_type', ['invoice', 'voucher', 'downpaymentinvoice'])->nullable();
            $table->string('creditreform_id')->nullable();
            $table->json('properties')->nullable();
            $table->boolean('status')->default(0)->index();
            $table->date('sorted_on')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
