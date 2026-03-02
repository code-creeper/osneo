<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\Ticket;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use WireElements\Pro\Components\Modal\Modal;

class TicketForm extends Modal
{
    use LogsActivity;

    public Ticket|int $ticket;

    public string $heading;
    public bool $editing = false;

    protected function rules(): array
    {
        return [
            'ticket.number' => [
                'required',
                'size:17',
                'regex:/^TKT-\d{6}-\d{6}$/',
                Rule::unique('tickets', 'number')->ignore($this->ticket->id)
            ]
        ];
    }

    public function mount(Ticket $ticket): void
    {
        $this->heading = __('Create Ticket');
        $this->ticket = $ticket;

        if ($this->ticket->id){
            $this->editing = true;
            $this->heading = __('Edit Ticket');

            $this->authorize('create', $ticket);
        } else {
            $this->authorize('create', Ticket::class);
        }
    }

    public function render(): View
    {
        $data = array();

        return view('livewire.forms.ticket-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->ticket->save();

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('Ticket updated')]
        ]);
    }
}
