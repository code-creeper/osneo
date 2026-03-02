<x-wire-elements-pro::bootstrap.modal on-submit="submit">
    <x-slot name="title">{{ __('Invoice Details') }}</x-slot>
    <div class="row g-2">
        <div class="col-12">
            <x-tab tab-content-classes="pe-0">
                <x-slot name="tabs">
                    <x-tab.item label="Lexoffice" id="lexoffice_payload" :active="true"/>
                    <x-tab.item label="Creditreform" id="creditreform_payload" />
                </x-slot>
                <x-tab.content id="lexoffice_payload" :active="true">
                    <pre class="scrollbar-light" style="height: 500px">{{ json_encode($invoice->lexoffice_payload->toArray(), JSON_PRETTY_PRINT) }}</pre>
                </x-tab.content>
                <x-tab.content id="creditreform_payload">
                    <pre class="scrollbar-light" style="height: 500px">{{ json_encode($invoice->creditreform_payload->toArray(), JSON_PRETTY_PRINT) }}</pre>
                </x-tab.content>
            </x-tab>
        </div>

    </div>
    <x-slot name="buttons">
        <button class="btn btn-sm btn-primary" type="button" wire:modal="close">
            {{ __('Close') }}
        </button>
    </x-slot>
</x-wire-elements-pro::bootstrap.modal>
