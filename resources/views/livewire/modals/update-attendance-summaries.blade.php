<x-wire-elements-pro::bootstrap.modal on-submit="submit" :$title>
    <div class="row g-2">
        <x-form-select2 label="User" name="userId" :options="$users" placeholder="Select User"/>
        <x-form-flatpickr wrapper-class="col-6" name="start_date" label="Start Date"/>
        <x-form-flatpickr wrapper-class="col-6" name="end_date" label="End Date"/>
    </div>

    <x-partials.modal-footer-buttons submit-text="Update"/>
</x-wire-elements-pro::bootstrap.modal>
