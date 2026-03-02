<x-wire-elements-pro::bootstrap.modal on-submit="submit" :$title>
    <x-errors/>

    @if($existingDocuments && $existingDocuments->count())
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="btn-close btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ __('The following documents already exist and could not be uploaded:') }}
            <ul class="list-group mt-2">
                @foreach($existingDocuments as $uploadedDocumentName => $existingDocument)
                    <li class="d-flex justify-content-between">
                        <span>{{ $uploadedDocumentName }}</span>
                        <a href="#" wire:modal="modals.document-viewer, @js(['document' => $existingDocument->id])">View Existing File</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <livewire:media-library wire:model="documents" :multiple="true"/>

    <x-slot name="buttons">
        <button class="btn btn-sm btn-success" type="submit" onclick="draggedFile = false">
            {{ __('Upload') }}
        </button>
        <button class="btn btn-sm btn-primary" type="button" wire:modal="close" onclick="draggedFile = false">
            {{ __('Cancel') }}
        </button>
    </x-slot>
</x-wire-elements-pro::bootstrap.modal>
