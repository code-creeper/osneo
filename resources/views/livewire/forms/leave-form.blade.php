<x-wire-elements-pro::bootstrap.modal on-submit="submit" :$title>

    <div class="row g-2">
        <x-errors/>

        @if($editing && user()->cannot('edit leaves without approval'))
            <x-partials.requires-approval-notice/>
        @endif

        @if(!in_array('user_id', $hiddenFields))
            <x-form-select2 label="User" name="leave.user_id" :options="$users" placeholder="Select User"/>
        @endif

        @if(!in_array('dates', $hiddenFields))
            <x-form-flatpickr name="dates" preset="dateRangePicker" label="Select Dates" />
        @endif

        <x-form-select name="leave.reason_id" placeholder="Select Reason" label="Reason" :options="$reasons" />

        @if(user()->can('tag leaves'))
            <x-form-select2 label="Tags" name="selectedTags" :options="$tags" :multiple="true"/>
        @endif

        @if($editing && user()->cannot('edit leaves without approval'))
            <x-form-textarea name="comments" label="Comments"/>
        @endif
    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
