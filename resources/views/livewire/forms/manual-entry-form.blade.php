<x-wire-elements-pro::bootstrap.modal on-submit="submit" :$title>
    <div class="row g-2">
        <x-errors/>

        <x-form-input
                name="duration" label="Duration" wrapper-class="col-4"
                placeholder="hh:mm"
                x-init="IMask($el, {
                    mask: '[*][0][0][0][0]{:}#0',
                    definitions: {
                        '#': /[0-5]/,
                        '*': /-/
                    }
                })"
        />

        <x-form-select2 wrapper-class="col-4" label="User" name="entry.user_id" :options="$users" placeholder="Select User"/>
        <x-form-flatpickr wrapper-class="col-4" name="date" label="Date"/>
        <x-form-textarea name="entry.comments" label="Comments"/>

        <div class="col-12">
            <x-form-checkbox wrapper-class="mb-2" name="entry.payout" label="Payout"/>
        </div>
    </div>
    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
