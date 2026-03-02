<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::table('leave_reasons')
            ->insertUsing(['name', 'color', 'deductible', 'paid'], function ($query) {
                $query->select('name', 'color')
                    ->addSelect(DB::raw("IF(name = 'Urlaub', 1, 0) AS deductible"))
                    ->addSelect(DB::raw("IF(JSON_UNQUOTE(JSON_EXTRACT(extra_attributes, '$.leave_category')) = 'unpaid', 0, 1) AS paid"))
                    ->from('reasons');
            });


        Schema::table('leave_days', function (Blueprint $table) {
            $table->dropForeign('leave_days_reason_id_foreign');
            $table->dropIndex('leave_days_reason_id_foreign');

            $table->foreign('reason_id')->references('id')->on('leave_reasons')->nullOnDelete();
        });


        Schema::table('leaves', function (Blueprint $table) {
            $table->foreign('reason_id')->references('id')->on('leave_reasons')->nullOnDelete();
        });

        Schema::drop('reasons');
    }
};
