<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ $heading }}</x-slot>

    <div class="row g-2">
        <x-form-input name="ticket.number" label="Ticket Number" x-mask="TKT-999999-999999"/>
    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
