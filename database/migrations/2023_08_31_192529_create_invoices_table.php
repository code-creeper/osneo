<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id');
            $table->string('lexoffice_id')->nullable();
            $table->json('lexoffice_payload')->nullable();
            $table->string('creditreform_id')->nullable();
            $table->json('creditreform_payload')->nullable();
            $table->enum('type', ['invoice', 'voucher', 'downpaymentinvoice'])->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
