<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ __('Report Damage') }}</x-slot>

    <div class="row g-2">
        <x-form-input wrapper-class="col-6" label="Type" name="damage.type"/>
        <x-form-input wrapper-class="col-6" label="Part" name="damage.part"/>

        @can('update damage status')
            <x-form-select name="damage.status_id" placeholder="Select Status" label="Status" :options="$damage_statuses"/>
        @endif

        <x-slot name="buttons">
            <button class="btn btn-sm btn-success" type="submit">{{ __('Save Changes') }}</button>
            <button class="btn btn-sm btn-primary" type="button" wire:modal="close">{{ __('Cancel') }}</button>
        </x-slot>
    </div>
</x-wire-elements-pro::bootstrap.modal>
