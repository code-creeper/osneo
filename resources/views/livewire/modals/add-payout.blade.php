<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ __('Add Payout') }}</x-slot>

    <div class="row g-2">
        <div class="col-12">
            <label class="form-label" for="duration">{{ __('Payout') }}</label>
            <input type="text" class="form-control @error('payout') is-invalid @enderror"
                   id="duration" wire:model.live="payout">
            <x-error field="payout"/>
        </div>
        <div class="col-12">
            <label class="form-label" for="comments">{{ __('Comments') }}</label>
            <input type="text" class="form-control @error('comments') is-invalid @enderror"
                   id="comments" wire:model.live="comments">
            <x-error field="comments"/>
        </div>
        <div class="col-12 d-flex justify-content-end">
            <div>
                <button type="button" class="btn btn-sm btn-primary" wire:modal="close">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-sm btn-success">{{ __('Add') }}</button>
            </div>
        </div>
    </div>
</x-wire-elements-pro::bootstrap.modal>
