<?php

namespace App\Livewire\Widgets;

use App\Models\User;
use DB;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class VacationWidget extends Component
{
    public string $vacationYear = '';
    public ?int $userId;
    public $shadow = true;

    public function mount(): void
    {
        $this->vacationYear = $this->vacationYear ?? now()->year;
        $this->userId =  $this->userId ?? auth()->id();
    }

    public function render(): View
    {
        $data = array();
        $data['vacationYears'] = array_map(
            fn($year) => now()->subYears($year)->format('Y'),
            range(0, 4)
        );

        $user = User::query()->find($this->userId);

        $prevYear = now()->year($this->vacationYear)->previous('year');

        $data['vacationThisYear'] = $user->getAnnualLeaveEntitlement($this->vacationYear);
        $data['vacationLastYear'] = $user->calculateLeaveBalance($prevYear);
        $data['leavesBalance'] = $user->calculateLeaveBalance($this->vacationYear);

        $data['leaveStats'] = DB::table('leave_days AS ld')
            ->select([
                DB::raw('SUM(CASE WHEN l.approved_by IS NOT NULL AND ld.date <= CURDATE() THEN 1 ELSE 0 END) AS leaves_taken'),
                DB::raw('SUM(CASE WHEN l.approved_by IS NOT NULL AND ld.date > CURDATE() THEN 1 ELSE 0 END) AS leaves_planned'),
                DB::raw('SUM(CASE WHEN l.approved_by IS NULL AND ld.date > CURDATE() THEN 1 ELSE 0 END) AS leaves_pending'),
            ])
            ->join('leaves AS l', 'ld.leave_id', '=', 'l.id')
            ->join('leave_reasons', 'ld.reason_id', '=', 'leave_reasons.id')
            ->where('l.user_id', $user->id)
            ->whereYear('ld.date', $this->vacationYear)
            ->where('leave_reasons.deductible', 1)
            ->first();

        return view('livewire.widgets.vacation', $data);
    }

}
