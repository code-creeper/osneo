<x-wire-elements-pro::bootstrap.modal on-submit="submit" :$title>
    <div class="row g-2">
        <x-errors/>
        @if($editing && user()->cannot('edit attendance without approval'))
            <x-partials.requires-approval-notice/>
        @endif

        @if(!$editing)
            @if(user()->can('create manual attendance for all') && !in_array('user_id', $hiddenFields))
                <x-form-select2 label="User" name="attendance.user_id" :options="$users" placeholder="Select User"/>
            @endif

            @if(!in_array('date', $hiddenFields))
                <x-form-flatpickr name="date" label="Date" />
            @endif
        @endif

        <x-form-flatpickr wrapper-class="col-6" name="checkin" label="Checkin" preset="timepicker"/>
        <x-form-flatpickr wrapper-class="col-6" name="checkout" label="Checkout" preset="timepicker"/>

        @if($editing && user()->cannot('edit attendance without approval'))
            <x-form-textarea name="comments" label="Comments"/>
        @endif
    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
