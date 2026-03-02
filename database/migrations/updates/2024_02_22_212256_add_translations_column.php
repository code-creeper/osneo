<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->json('translations')->nullable();
        });

        Schema::table('document_properties', function (Blueprint $table) {
            $table->json('translations')->nullable();
        });

        Schema::table('leave_reasons', function (Blueprint $table) {
            $table->json('translations')->nullable();
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('role_ids');
        });

        Schema::table('constants', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('fields');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('primary');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->json('translations')->nullable();
        });

        Schema::table('service_categories', function (Blueprint $table) {
            $table->json('translations')->nullable();
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('model');
        });
    }
};
