<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ $heading }}</x-slot>

    <div class="row g-2">
        <x-form-input wrapper-class="col-6" name="vehicle.license_plate" label="License Plate"/>
        <x-form-input wrapper-class="col-6" name="vehicle.ticket_number" label="Ticket Number"/>
        <x-form-input wrapper-class="col-6" name="vehicle.manufacturer" label="Manufacturer"/>
        <x-form-input wrapper-class="col-6" name="vehicle.model" label="Model"/>
    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
