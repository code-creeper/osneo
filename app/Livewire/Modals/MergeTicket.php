<?php

namespace App\Livewire\Modals;

use App\Livewire\Traits\LogsActivity;
use App\Models\Ticket;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class MergeTicket extends Modal
{
    use LogsActivity;

    public Ticket|int $baseTicket;
    public int $ticketId;

    public string $heading;

    public function mount(Ticket $ticket): void
    {
        $this->baseTicket = $ticket;
    }

    public function render(): View
    {
        $data = array();

        return view('livewire.modals.merge-ticket', $data);
    }

    public function submit(): void
    {
        $this->validate([
            'ticketId' => 'required'
        ]);

        $this->baseTicket->documents()->update([
            'ticket_id' => $this->ticketId
        ]);

        $this->baseTicket->delete();

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('Ticket merged')],
        ]);
    }
}
