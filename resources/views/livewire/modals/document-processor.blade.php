<x-wire-elements-pro::bootstrap.modal on-submit="submit" :$title>
    <div class="row gx-2 align-items-center">
        <div class="col-auto">
            <input class="form-control mb-2" type="date" value="{{ date('Y-m-d') }}" readonly>
        </div>
        <div class="col-auto">_</div>
        <div class="col-auto">
            <input name="source" class="form-control mb-2" type="text" :value="$wire.source">
        </div>
        <div class="col-auto">_</div>
        <div class="col-auto">
            <input name="document_id" class="form-control mb-2" type="text" :value="$wire.documentType">
        </div>
        <div class="col-auto">_XXX_XXX_XXX_XXX.pdf</div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <iframe height="500" width="100%" wire:ignore src="{{ $document->getUrl() }}"></iframe>
        </div>
        <div class="col-lg-4">
            <div class="row g-3">

                @wire('live')
                <x-form-group class="col-12" label="Document Source" inline>
                    @foreach($document_sources as $sourceType)
                        <x-form-radio name="source" :value="$sourceType->name" :label="$sourceType->value"/>
                    @endforeach
                </x-form-group>

                <x-form-select2
                        name="documentType" label="Document Type" :placeholder="__('Select Document Type')"
                        :options="$documentTypes->toKeyValuePair(key: 'key')"
                />
                @endwire

            </div>

            <div>
                @if($documentType)
                    <hr>
                    <h5 class="text-center" wire:loading wire:target="documentType">{{ __('Loading options...') }}</h5>
                    <div class="row g-3" wire:loading.remove wire:target="documentType">
                        @forelse ($documentProperties as $property)
                            <x-document-property :property="$property" :wire:key="'property-'.$property->id"/>
                        @empty
                            <p>{{ __('No option available') }}</p>
                        @endforelse

                        <div class="mb-3 d-grid">
                            <button type="submit" class="btn btn-primary">{{ __('Sort') }}</button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-wire-elements-pro::bootstrap.modal>
