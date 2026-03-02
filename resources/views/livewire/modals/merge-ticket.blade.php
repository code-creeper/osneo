<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{  __('Merge this Ticket') }}</x-slot>

    <div class="row g-2" style="height: 15rem">
        <x-form-select2 label="Base Ticket" name="ticketId" source="tickets" placeholder="Select Base Ticket"/>
    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
