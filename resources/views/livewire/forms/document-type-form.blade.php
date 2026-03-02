<x-wire-elements-pro::bootstrap.modal on-submit="submit" :$title>
    <div class="row g-2">
        <x-errors/>
        <x-form-input wrapper-class="col-6" name="documentType.key" label="Key"/>
        <x-multilingual-form-input wrapper-class="col-6" name="name" label="Name"/>
        <x-form-select2 label="Subscribers" name="subscriberIds" :options="$users" :multiple="true"/>

        <div class="col-12 mt-3">
            <x-form-checkbox name="documentType.lexoffice" label="Lexoffice" :custom-control="false"/>
        </div>
    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
