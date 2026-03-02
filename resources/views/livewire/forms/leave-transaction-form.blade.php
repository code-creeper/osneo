<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ __('Leave Adjustment of :user', ['user' => $user->name]) }}</x-slot>

    <div class="row g-2" >
        <x-form-input name="amount" label="Amount" type="number"/>
        <x-form-textarea name="comments" label="Comments"/>
        <x-form-flatpickr name="transacted_on" label="Transacted On"/>
    </div>

    <x-partials.modal-footer-buttons submit-text="Submit"/>
</x-wire-elements-pro::bootstrap.modal>
