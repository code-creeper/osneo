<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('manual_entries', function (Blueprint $table) {
            $table->integer('duration')->change();
        });

        DB::table('manual_entries')->where('type', 'break')->update([
            'duration' => DB::raw( '0 - duration' )
        ]);

        Schema::table('manual_entries', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('manual_entries', function (Blueprint $table) {
            //
        });
    }
};
