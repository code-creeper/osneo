<x-wire-elements-pro::bootstrap.modal on-submit="save" :title="$document->name()">
    <iframe height="800" width="100%"  src="{{ $document->getUrl() }}"></iframe>

    <x-slot name="buttons">
        <button class="btn btn-sm btn-primary" type="button" wire:modal="close, {force: @js($forceClose)}">
            {{ __('Close') }}
        </button>
    </x-slot>
</x-wire-elements-pro::bootstrap.modal>
