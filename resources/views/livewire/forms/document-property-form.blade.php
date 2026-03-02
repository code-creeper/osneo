<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-errors/>
    <x-slot name="title">{{ $heading }}</x-slot>

    <div class="row g-2">
        <x-form-input wrapper-class="col-6" name="documentProperty.key" label="Key"/>
        <x-form-input wrapper-class="col-6" name="documentProperty.name" label="Name"/>

        <x-form-select
            wrapper-class="col-6" name="documentProperty.type"
            label="Type" :options="$propertyTypes" placeholder="Select"
        />

        <x-form-input wrapper-class="col-6" name="documentProperty.order" label="Order" type="number"/>

        <x-form-select
            wrapper-class="col-6" name="documentProperty.active"
            label="Status" :options="[1 => 'Enabled', 0 => 'Disabled']"
        />

        <x-form-select
            wrapper-class="col-6" name="documentProperty.is_name"
            label="Is Name" :options="[1 => 'Yes', 0 => 'No']"
        />

        <x-form-input wrapper-class="col-12" name="documentProperty.rules" label="Rules"/>
    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
