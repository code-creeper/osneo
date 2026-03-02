<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->longText('body')->nullable()->change();

            $table->after('body', function () use ($table) {
                $table->enum('audience', ['all', 'role', 'user'])->default('all');
                $table->text('role_ids')->nullable();
            });
        });

        Schema::table('announcement_user', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('user_id');
        });

        DB::table('announcements')->update([
            'audience' => 'user'
        ]);
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['audience', 'role_ids']);
        });

        Schema::table('announcement_user', function (Blueprint $table) {
            $table->dropColumn('read_at');
        });
    }
};
