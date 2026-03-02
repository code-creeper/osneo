<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;

class ExportPayrollController extends Controller
{
    public function __invoke()
    {
        $data = array();
        $user = User::latest()->first();
        $selectedMonth = now()->subMonth()->format('Y-m');
        $year = now()->year;
        $prevYear = now()->subYear()->year;

        $data['payrolls'] = Payroll::with('user')->limit(5)->get();

        $data['leavesByReason'] = DB::table('leave_days as ld')
            ->select('r.name')
            ->selectRaw("SUM(CASE WHEN DATE_FORMAT(ld.date, '%Y-%m') = '$selectedMonth' THEN 1 ELSE 0 END) as leaves_this_month")
            ->selectRaw("SUM(CASE WHEN YEAR(ld.date) = '$year' THEN 1 ELSE 0 END) as leaves_this_year")
            ->join('leave_reasons as r', 'ld.reason_id', '=', 'r.id')
            ->where('ld.user_id', $user->id)
            ->groupBy('r.name')
            ->havingRaw('leaves_this_month > 0 OR leaves_this_year > 0')
            ->get();

        $data['vacationThisYear'] = $user->getAnnualLeaveEntitlement($year);
        $data['vacationLastYear'] = $user->calculateLeaveBalance($prevYear);
        $data['leavesBalance'] = $user->calculateLeaveBalance($year);

        $pdf = Pdf::loadView('pdf.payroll', $data);

        return $pdf->download('payroll.pdf');
    }
}
