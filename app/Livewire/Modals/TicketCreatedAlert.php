<?php

namespace App\Livewire\Modals;

use App\Models\Ticket;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class TicketCreatedAlert extends Modal
{
    public Ticket|int $ticket;

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;

        $ticket->update([
            'synced' => 1
        ]);
    }

    public function render(): View
    {
        return view('livewire.modals.ticket-created-alert');
    }
}
