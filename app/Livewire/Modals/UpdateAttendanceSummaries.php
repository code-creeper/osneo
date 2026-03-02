<?php

namespace App\Livewire\Modals;

use App\Jobs\UpdateAttendanceSummariesJob;
use App\Livewire\Traits\LogsActivity;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use WireElements\Pro\Components\Modal\Modal;

class UpdateAttendanceSummaries extends Modal
{
    use LogsActivity;

    public ?int $userId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function render() :View
    {
        $data = [];

        $data['title'] = __('Update Summaries');
        $data['users'] = User::relevant()->get()->toKeyValuePair();

        return view('livewire.modals.update-attendance-summaries', $data);
    }

    public function submit(): void
    {
        $this->startDate = $startDate = $this->startDate
            ? Carbon::parse($this->startDate)
            : Attendance::query()
                ->when($this->userId, fn($query) => $query->whereUserId($this->userId))
                ->oldest()
                ->value('date');

        $endDate = $this->endDate ? Carbon::parse($this->endDate) : now();

        $this->validate([
            'startDate' => 'required'
        ]);

        UpdateAttendanceSummariesJob::dispatch($startDate, $endDate, $this->userId);

        $this->close(andDispatch: [
            'flashNotification' => ['message' => __('Summaries are being updated now')],
        ]);
    }
}
