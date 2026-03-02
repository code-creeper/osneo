<x-wire-elements-pro::bootstrap.modal on-submit="submit" :$title>
    <div class="row g-2">
        <x-form-input name="serviceCategory.name" label="Name"/>
        <x-form-textarea name="serviceCategory.description" label="Description"/>
    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
