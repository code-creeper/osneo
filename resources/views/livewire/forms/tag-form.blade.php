<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ $heading }}</x-slot>

    <div class="row g-2">
        <x-form-input name="tag.name" label="Name"/>
        <x-form-select name="tag.model" :options="$modules" label="Model" placeholder="Select"/>
        <x-form-input name="tag.color" type="color" label="Color"/>
    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
