<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        DB::statement("ALTER TABLE modifications MODIFY COLUMN type ENUM('create', 'edit', 'delete', 'restore')");
    }

    public function down()
    {
        DB::statement("ALTER TABLE modifications MODIFY COLUMN type ENUM('edit', 'delete')");
    }
};
