<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('constants', function (Blueprint $table) {
            $table->string('group_id')->change();
            $table->longText('name')->change();
        });

        Schema::table('constants', function (Blueprint $table) {
            $table->renameColumn('group_id', 'group');
            $table->renameColumn('name', 'value');
        });
    }

    public function down(): void
    {
        Schema::table('constants', function (Blueprint $table) {
            $table->renameColumn('group', 'group_id');
            $table->renameColumn('value', 'name');
        });
    }
};
