<x-wire-elements-pro::bootstrap.modal>
    <x-slot name="title">{{ __('New Document Created') }}</x-slot>

    <div class="card-body d-grid text-center">
        <h5 class="card-title text-primary">{{ __('A new process with the number :ticket is created.', ['ticket' => $ticket->number]) }}</h5>
        <p class="card-text">{{ __('Please attach the document link to the pia process.') }}</p>
    </div>

    <x-slot name="buttons">
        <button type="button" class="btn btn-sm btn-success" x-clipboard.raw="{{ route('ticket.documents', $ticket->number) }}">
            {{ __('Copy Link') }}
        </button>
        <button class="btn btn-sm btn-primary" type="button" wire:modal="close, {force: true}">
            {{ __('Close') }}
        </button>
    </x-slot>
</x-wire-elements-pro::bootstrap.modal>
