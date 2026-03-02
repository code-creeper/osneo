<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->softDeletes();
        });

        Schema::table('leave_transactions', function (Blueprint $table) {
            $table->date('transacted_on')->nullable()->after('transacted_by');
        });

        \App\Models\Leave::where('ends_on', '<=', '2021-12-31')->get()->each(fn($leave) => $leave->delete());

        $this->updateRecords();
        $this->regenerateLeaveTransactions();
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn(['approved_at', 'rejected_at']);
        });

        Schema::table('leave_transactions', function (Blueprint $table) {
            $table->dropColumn('transacted_on');
        });
    }

    private function updateRecords()
    {

        DB::table('leaves')->whereNull('created_at')->update([
            'created_at' => DB::raw("TIMESTAMP(starts_on)")
        ]);

        DB::table('leaves')->whereNull('updated_at')->update([
            'updated_at' => DB::raw("created_at")
        ]);

        DB::table('leaves')->whereNotNull('rejected_by')->update([
            'rejected_at' => DB::raw("created_at")
        ]);

        DB::table('leave_transactions')->update([
            'transacted_on' => DB::raw("DATE(created_at)")
        ]);

        DB::table('leaves')
            ->leftJoin('leave_transactions', 'leaves.id', '=', 'leave_transactions.leave_id')
            ->select('leaves.id', DB::raw('COALESCE(MAX(leave_transactions.created_at), leaves.updated_at) AS latest_date'))
            ->whereNotNull('approved_by')
            ->groupBy('leaves.id')
            ->chunkById(1000, function($leaves) {
                foreach ($leaves as $leave) {
                    DB::table('leaves')
                        ->where('id', '=', $leave->id)
                        ->update(['approved_at' => $leave->latest_date]);
                }
            });
    }

    private function regenerateLeaveTransactions()
    {
        DB::statement("Create table if not exists leave_transactions_temp like leave_transactions");

        Schema::table('leaves', function (Blueprint $table) {
            $table->integer('days')->nullable()->change();
        });

        $transactions = DB::table('leave_transactions as lt')
            ->select(
                'lt.user_id',
                'lt.leave_id',
                'lt.transacted_by',
                'lt.created_at as transacted_at',
                'lt.amount',
                'lt.balance',
                'lt.comments',
                'lt.created_at',
                'lt.updated_at'
            )
            ->whereNotNull('lt.transacted_by')
            ->union(
                DB::table('leaves as l')
                    ->select(
                        'l.user_id',
                        DB::raw('l.id as leave_id'),
                        DB::raw('NULL as transacted_by'),
                        'l.approved_at as transacted_at',
                        DB::raw('-1 * l.days as amount'),
                        DB::raw('0 as balance'),
                        DB::raw("'Leave approved' as comments"),
                        DB::raw('approved_at as created_at'),
                        DB::raw('approved_at as updated_at')
                    )
                    ->whereNotNull('l.approved_at')
                    ->where('l.ends_on', '>', '2021-12-31')
                    ->where('l.reason_id', 7)
            )
            ->orderBy('transacted_at', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->get();

        DB::table('leave_transactions_temp')->truncate();

        foreach ($transactions as $transaction){
            $balance = DB::table('leave_transactions_temp')->where('user_id', $transaction->user_id)->sum('amount');
            DB::table('leave_transactions_temp')->insert([
                'user_id' => $transaction->user_id,
                'leave_id' => $transaction->leave_id,
                'transacted_by' => $transaction->transacted_by,
                'transacted_on' => DB::raw("DATE('$transaction->transacted_at')"),
                'amount' => $transaction->amount,
                'balance' => $balance + $transaction->amount,
                'comments' => $transaction->comments,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
            ]);
        }

        Schema::drop('leave_transactions');
        Schema::rename('leave_transactions_temp', 'leave_transactions');

        Schema::table('leaves', function (Blueprint $table) {
            $table->unsignedInteger('days')->nullable()->change();
        });
    }
};
