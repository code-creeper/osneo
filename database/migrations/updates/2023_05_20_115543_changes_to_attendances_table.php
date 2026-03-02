<?php

use App\Jobs\UpdateAttendanceSummariesJob;
use App\Models\AttendanceSummary;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN date DATE AFTER logged_by");

        Schema::table('attendances', function (Blueprint $table) {
            $table->after('logged_by', function (Blueprint $table) {
                $table->timestamp('checkin');
                $table->timestamp('checkout')->nullable();
            });
        });

        // update existing attendances and copy data from attendance entries to attendances
        DB::table('attendances')->update([
            'checkin' => DB::raw('(SELECT logged_at FROM attendance_entries WHERE attendance_entries.attendance_id = attendances.id AND attendance_entries.type = "checkin")'),
            'checkout' => DB::raw('(SELECT logged_at FROM attendance_entries WHERE attendance_entries.attendance_id = attendances.id AND attendance_entries.type = "checkout")'),
            'date' => DB::raw('(SELECT DATE(logged_at) FROM attendance_entries WHERE attendance_entries.attendance_id = attendances.id AND attendance_entries.type = "checkin")'),
            'logged_by' => DB::raw('(SELECT logged_by FROM attendance_entries WHERE attendance_entries.attendance_id = attendances.id AND attendance_entries.type = "checkout")'),
            'comments' => DB::raw('(SELECT comments FROM attendance_entries WHERE attendance_entries.attendance_id = attendances.id AND attendance_entries.type = "checkout")'),
        ]);

        DB::table('attendances')
            ->whereNotNull('comments')
            ->update([
                'comments' => null,
                'checkout' => null,
                'duration' => null
            ]);

        // cleanup attendances
        DB::table('attendances')->whereDate('date', '0000-00-00')->delete();
        DB::table('attendances')->whereNotNull('deleted_at')->whereNull('duration')->delete();
        DB::table('attendances')->where('duration', 0)->delete();

        // DB::table('attendance_entries')->delete();

        // summaries

        DB::statement("ALTER TABLE attendance_summaries MODIFY COLUMN date DATE AFTER user_id");
        Schema::table('attendance_summaries', function (Blueprint $table) {
            $table->renameColumn('target_hours', 'target_time');
            $table->renameColumn('working_hours', 'working_time');
        });

        AttendanceSummary::truncate();
        Schema::table('attendance_summaries', function (Blueprint $table) {

            $table->unsignedInteger('target_time')->default(0)->change();
            $table->unsignedInteger('working_time')->default(0)->change();

            $table->after('working_time', function (Blueprint $table) {
                $table->unsignedInteger('paid_time')->default(0)->comment('Paid leaves time');
                $table->unsignedInteger('manual_time')->default(0);
                $table->integer('payout_time')->default(0)->comment('Paid overtime');
                $table->integer('overtime')->default(0);

                $table->boolean('leave')->default(0);
                $table->boolean('off_day')->default(0);
                $table->boolean('holiday')->default(0);
                $table->boolean('weekend')->default(0);
            });

            $table->unique(['date', 'user_id']);

            $table->dropTimestamps();
        });

        $this->updateAttendanceSummaries();
    }

    private function updateAttendanceSummaries(): void
    {
        foreach (User::all() as $user){
            if ( ! $user->attendances()->first()) {
                continue;
            }

            $startDate = $user->attendances()->first()->date;
            $endDate = $user->deactivate_on ? $user->deactivate_on : now();

            UpdateAttendanceSummariesJob::dispatch($startDate, $endDate, $user->id);
        }
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            //
        });
    }
};
