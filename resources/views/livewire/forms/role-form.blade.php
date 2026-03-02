<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ $heading }}</x-slot>
    <div class="row g-2">
        <x-form-input wrapper-class="col-6" name="role.name" label="Name"/>
        <x-form-input wrapper-class="col-6" name="role.display_name" label="Display Name"/>
    </div>
    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
