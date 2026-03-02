<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ __($heading) }}</x-slot>

    <div class="row g-3">
        <x-form-select2 label="Allowed Document Types" name="preferences.allowed_document_types" :options="$documentTypes" :multiple="true"/>
        <x-form-input name="preferences.leave_increment_start_year" label="Leave Increment Start Year"/>
        <x-form-input name="preferences.leave_increment_per_year" label="Leave Increment Per Year"/>
    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
