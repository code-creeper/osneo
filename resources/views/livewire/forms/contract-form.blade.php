<x-wire-elements-pro::bootstrap.modal on-submit="submit" :loader="false">
    <x-slot name="title">{{ $heading }}</x-slot>
    <x-errors/>

    <div class="row g-2">

        <x-form-select2 name="contract.contact_id" placeholder="Select Contact" :options="$contacts">
            <x-slot:label>
                <label class="form-label">
                    {{ __('Contact') }}
                    @can('create contacts')
                        <a href="javascript:void(0)" wire:modal="forms.contact-form" class="small ms-2">
                            {{ __('Create +') }}
                        </a>
                    @endif
                </label>
            </x-slot:label>
        </x-form-select2>

        <div class="col-12">
            <h5 class="mb-0">{{ __('Services') }}</h5>
            <hr>
            <div class="row">
                <h5 class="col-3">{{ __('Category') }}</h5>
                <h5 class="col-3">{{ __('Service') }}</h5>
                <h5 class="col-3">{{ __('Size') }}</h5>
                <h5 class="col-1">{{ __('Unit') }}</h5>
                <h5 class="col-1">{{ __('Price') }}</h5>
                <div class="col-1"></div>
            </div>

            @foreach($contractServices as $index => $service)
                <div class="row g-2 mb-2" wire:key='{{ str()->uuid() }}' wire:loading.class="disabled-link">

                    @wire('live')
                    <x-form-select
                        wrapper-class="col-3" name="contractServices.{{$index}}.category_id"
                        :options="$categories" :placeholder="__('Select Category')"
                    />
                    <x-form-select
                        wrapper-class="col-3" name="contractServices.{{$index}}.service_id"
                        :options="$services[$index]" :placeholder="__('Select Service')"
                    />

                    <x-form-select
                        wrapper-class="col-3" name="contractServices.{{$index}}.size_id"
                        :options="$sizes[$index]" :placeholder="__('Select Size')"
                    />
                    @endwire

                    <x-form-input
                        wrapper-class="col-1" name="unit" disabled
                        :value="$service['unit'] ?? ''" placeholder="Unit"
                    />

                    <x-form-input
                        wrapper-class="col-1" name="unit" disabled placeholder="Price"
                        :value="money($service['price']  ?? 0, forceDecimals: true)"
                    />

                    <div class="col-1 text-end">
                        <button type="button" class="btn btn-danger"
                              wire:loading.attr="disabled"
                              wire:click="removeService({{$index}})">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </div>
                </div>
            @endforeach
            <div class="form-group mb-3">
                <button type="button" class="btn btn-primary"
                        wire:loading.attr="disabled"
                        wire:click.prevent="addService">
                    <i class="la la-plus"></i>{{ __('Add Service') }}
                </button>
            </div>
        </div>

        <div class="row g-2">
            <h5 class="mb-0">{{ __('Sections') }}</h5>
            <hr>
            <x-form-input name="ticketNumber" label="Ticket" insert="ticket"/>
            <x-form-input name="sections.title" label="Title"/>
            <x-form-textarea name="sections.introduction" label="Introduction"/>
            <x-form-input wrapper-class="col-6" name="sections.payment_terms.label" label="Payment Term Label"/>
            <x-form-input wrapper-class="col-6" name="sections.payment_terms.duration" label="Payment Term Duration"/>
            <x-form-textarea name="sections.remarks" label="Remarks"/>
        </div>

    </div>

    <x-slot name="buttons">
        <div class="text-end mt-2">
            @if(!$formSubmitted)
                <button class="btn btn-sm btn-success" type="submit" wire:key="submit_btn">
                    {{ __('Save Changes') }}
                </button>
            @endif
            <button class="btn btn-sm btn-primary" type="button" wire:modal="close" wire:key="cancel_btn">
                {{ __('Cancel') }}
            </button>
        </div>
    </x-slot>
</x-wire-elements-pro::bootstrap.modal>
