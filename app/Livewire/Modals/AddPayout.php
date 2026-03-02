<?php

namespace App\Livewire\Modals;

use App\Livewire\Traits\LogsActivity;
use App\Models\User;
use App\Notifications\ManualEntryNotification;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Throwable;
use WireElements\Pro\Components\Modal\Modal;

class AddPayout extends Modal
{
    use LogsActivity;

    public mixed $payout;
    public mixed $maxOvertime;
    public User|int $user;
    public string $monthWithYear;
    public string $comments;
    public Carbon $date;

    public function mount(User $user, int $overtime, string $monthWithYear): void
    {
        $this->user = $user;

        // if overtime is negative, we add payout
        // if overtime is positive, we subtract payout
        $overtime *= -1;
        $this->payout = minutesToDurationInput(hrToMins($overtime));
        $this->monthWithYear = $monthWithYear;
        $this->maxOvertime = $overtime;

        if ($this->payout == 0) {
            $this->payout = null;
        }

        try {
            $this->date = Carbon::createFromFormat('Y-m', $monthWithYear)->endOfMonth();
        } catch (Throwable $e) {
            throw new \ErrorException('Wrong format provided. Format must be in \'Y-m\' form');
        }

        $this->comments = __('Paid overtime for the month of :month :year', [
            'month' => __($this->date->format('F')),
            'year' => $this->date->format('Y'),
        ]);
    }

    public function render(): View
    {
        return view('livewire.modals.add-payout');
    }

    public function submit(): void
    {
        $this->validate([
            'payout' => "required",
        ]);

        $duration = durationInputToMinutes($this->payout);

        if ( ! $duration) {
            $this->dispatch(
                'flashNotification',
                message: __('Payout must be a number'),
                type: 'error'
            );

            return;
        }

        $entry = $this->user->manualEntries()->create([
            'date' => $this->date,
            'duration' => $duration,
            'logged_by' => auth()->id(),
            'type' => getAttendanceTypeFromDuration($this->payout),
            'comments' => $this->comments,
            'payout' => 1,
        ]);

        if ($entry->user_id !== auth()->id()){
            $entry->user->notify(new ManualEntryNotification($entry));
        }

        $this->close(andDispatch: [
            'updateWorkingHoursOverview',
            'flashNotification' => [
                'message' => __('Payout added successfully')
            ]
        ]);
    }
}
