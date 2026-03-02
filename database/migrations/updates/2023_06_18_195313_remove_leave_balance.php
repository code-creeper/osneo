<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::table('leave_transactions')->whereNotNull('leave_id')->delete();

        Schema::table('leave_transactions', function (Blueprint $table) {
            $table->dropColumn('leave_id');
            $table->dropColumn('balance');
        });
    }

    public function down(): void
    {
        Schema::table('', function (Blueprint $table) {
            //
        });
    }
};
