<x-wire-elements-pro::bootstrap.modal on-submit="submit" :title="$title">

    <x-form-select2 label="Users" name="selectedUsers" :multiple="true" :options="$users"/>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
