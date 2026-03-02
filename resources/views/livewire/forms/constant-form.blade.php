<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ $heading }}</x-slot>

    <div class="row g-2">
        <x-form-input wrapper-class="col-6" name="constant.group" label="Group"/>
        <x-form-input wrapper-class="col-6" name="constant.key" label="Key"/>
        <x-form-textarea name="constant.value" label="Value"/>
    </div>

    <x-partials.modal-footer-buttons/>

</x-wire-elements-pro::bootstrap.modal>
