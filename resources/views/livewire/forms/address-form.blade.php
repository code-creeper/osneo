<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ $heading }}</x-slot>
    <x-errors/>
    <div class="row g-2">

        <x-form-checkbox wrapper-class="ms-1" label="Service Location" name="address.is_service_location"/>
        <x-form-input name="address.street" label="Street"/>
        <x-form-input name="address.zip_code" label="Zip Code"/>
        <x-form-input name="address.city" label="City"/>

        <template x-if="$wire.address.is_service_location">
            <x-form-select
                    name="address.heating_system" label="Heating System"
                    placeholder="Select Heating System" :options="$heatingSystems"
            />
        </template>

    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
