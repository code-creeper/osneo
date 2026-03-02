<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ $heading }}</x-slot>

    <div class="row g-2">
        <x-form-input type="date" name="date" label="Date"/>
    </div>

    <x-partials.modal-footer-buttons submit-text="Create"/>

</x-wire-elements-pro::bootstrap.modal>
