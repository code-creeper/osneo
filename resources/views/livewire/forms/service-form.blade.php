<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ $heading }}</x-slot>

    <x-errors keys="sizes"/>
    <div class="row g-2">
        <x-form-select2 label="Category" name="service.service_category_id" :options="$categories" placeholder="Select Category">
            <x-slot:label>
                <label class="form-label">
                    {{ __('Category') }}
                    @can('create service categories')
                        <a href="javascript:void(0)" wire:modal="forms.service-category-form" class="small ms-2">{{ __('Create +') }}</a>
                    @endif
                </label>
            </x-slot:label>
        </x-form-select2>

        <x-form-input name="service.name" label="Name"/>
        <x-form-input name="service.unit" label="Unit"/>

        <x-form-textarea name="service.description">
            <x-slot:label>
                {{ __('Description') }}

                @if($descriptionCanBeCopied)
                    <a href="javascript:void(0)" wire:click="copyCategoryDescription"
                       class="small ms-2">{{ __('Copy description from category') }}</a>
                @endif
            </x-slot:label>
        </x-form-textarea>

        <div class="col-12">
            <h5>{{ __('Sizes') }}</h5>
            @foreach($sizes as $index => $size)
                <div class="row g-2 mb-2">
                    <x-form-input wrapper-class="col" name="sizes.{{$index}}.name" placeholder="Size"/>
                    <x-form-input wrapper-class="col" name="sizes.{{$index}}.price" placeholder="Price" type="number" step=".01"/>

                    <div class="col-1 text-end">
                        <span type="button" class="btn btn-danger" wire:click="removeSize({{$index}})">
                            <i class="fa fa-trash-o"></i>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="form-group mb-3">
            <button type="button" class="btn btn-sm btn-primary" wire:click.prevent="addSize">
                <i class="la la-plus"></i>{{ __('Add Size') }}
            </button>
        </div>
    </div>

    <x-partials.modal-footer-buttons/>
</x-wire-elements-pro::bootstrap.modal>
