<?php

use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DocumentTypeSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Pest\Laravel\seed;

return new class extends Migration {
    public function up()
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key');
            $table->boolean('lexoffice')->default(0);
            $table->json('subscriber_ids')->nullable();
            $table->json('translations')->nullable();
        });

        try {
            seed(DocumentTypeSeeder::class);
        } catch (\Exception $exception){
            $this->down();
            throw new \Exception($exception->getMessage());
        }
    }

    public function down()
    {
        Schema::dropIfExists('document_types');
    }
};
