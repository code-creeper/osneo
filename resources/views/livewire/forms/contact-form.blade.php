<x-wire-elements-pro::bootstrap.modal on-submit="submit" :$title>
    <div class="row g-2">
        <x-form-checkbox wrapper-class="col-4" label="Company" name="contact.is_company"/>
        <x-form-checkbox wrapper-class="col-4" label="Customer" name="contact.is_customer"/>
        <x-form-checkbox wrapper-class="col-4" label="Supplier" name="contact.is_supplier"/>

        <template x-if="$wire.contact.is_company">
            <x-form-input name="contact.name" label="Company Name"/>
        </template>

        <template x-if="!$wire.contact.is_company">
            <x-form-input wrapper-class="col-6" name="contact.first_name" label="First Name"/>
        </template>

        <template x-if="!$wire.contact.is_company">
            <x-form-input wrapper-class="col-6" name="contact.last_name" label="Last Name" />
        </template>

        <x-form-input wrapper-class="col-6" name="contact.phone" label="Telephone"/>
        <x-form-input wrapper-class="col-6" name="contact.email" label="Email"/>

        <template x-if="$wire.contact.is_customer">
            <x-form-select
                    name="contact.customer.invoice_method" label="Invoice Method"
                    :options="['Email' => 'Email', 'Post' => 'Post']"
            />
        </template>

        <x-form-select2 label="Management Company" name="contact.manager_id" placeholder="Select Company" :options="$companies"/>

        @wire(false)
        <x-form-input name="contact.billing_address_id" :default="$address" disabled>
            <x-slot:label>
                {{ __('Address') }}
                <a href="javascript:void(0)" wire:modal="forms.address-form" class="small ms-2">{{ __('Create +') }}</a>
            </x-slot:label>

        </x-form-input>
        @endwire

    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
