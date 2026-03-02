<?php

namespace App\Livewire\Modals;

use App\Livewire\Traits\LogsActivity;
use App\Models\ManualEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Throwable;
use WireElements\Pro\Components\Modal\Modal;

class AutoCheckoutEntries extends Modal
{
    use LogsActivity;

    public User|int $user;
    public string $monthWithYear;
    public Carbon $date;

    public function mount(User $user, string $monthWithYear): void
    {
        $this->user = $user;
        $this->monthWithYear = $monthWithYear;

        try {
            $this->date = Carbon::createFromFormat('Y-m', $monthWithYear)->endOfMonth();
        } catch (Throwable $e) {
            throw new \ErrorException('Wrong format provided. Format must be in \'Y-m\' form');
        }
    }

    public function render(): View
    {
        $data = array();

        $data['attendances'] = $this->user->attendances()
            ->withEntries()
            ->whereMonthOfYear('date', $this->date->month, $this->date->year)
            ->whereHas('entries',
                fn($query) => $query->autoCheckedOut()
            )->get();

        return view('livewire.modals.auto-checkout-entries', $data);
    }
}
