<?php

use App\Models\Employment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employments', function (Blueprint $table) {
            $table->renameColumn('target_hours_type', 'employment_type');
            $table->renameColumn('target_hours', 'weekly_target_time');
        });

        Schema::table('employments', function (Blueprint $table) {
            $table->unsignedInteger('weekly_target_time')->default(0)->change();
            $table->float('hourly_rate')->default(0)->after('off_days');
        });

        Schema::table('employments', function (Blueprint $table) {
            $table->unsignedInteger('monthly_target_time')->default(0)->after('weekly_target_time');
        });

        $this->convertTargetTimeToMinutes();
        $this->addWeekdaysToEmployments();
    }

    private function convertTargetTimeToMinutes(): void
    {
        DB::table('employments')->update([
            'weekly_target_time' => DB::raw("weekly_target_time * 60"),
        ]);
    }

    private function addWeekdaysToEmployments(): void
    {
        foreach (Employment::all() as $employment){
            $employment->off_days = array_unique(array_merge($employment->off_days, [
                'saturday', 'sunday'
            ]));

            $employment->save();
        }
    }

    public function down(): void
    {
        Schema::table('employments', function (Blueprint $table) {
            //
        });
    }
};
