<?php

namespace App\Livewire\Modals;

use App\Livewire\Traits\LogsActivity;
use App\MediaLibrary\Media;
use App\Models\Document;
use App\Traits\HasWireElementsPlaceholder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Spatie\MediaLibraryPro\Livewire\Concerns\WithMedia;
use WireElements\Pro\Components\Modal\Modal;

class DocumentsUploader extends Modal
{
    use LogsActivity;

    use HasWireElementsPlaceholder;

    use WithMedia;
    use WithFileUploads;

    public array $documents = [];
    public Collection $existingDocuments;

    public function mount(): void
    {
        $this->existingDocuments = collect();
        $this->authorize('upload documents');
    }

    public function render(): View
    {
        $data = array();
        $data['title'] = __('Upload Document');

        return view('livewire.modals.documents-uploader', $data);
    }

    public function submit(): void
    {
        $this->existingDocuments = collect();

        $this->validate([
            'documents' => 'required',
        ]);

        foreach ($this->documents as $uuid => $document) {
            $file = Media::where('uuid', $uuid)->first();
            $pdf = Storage::get($file->getPath());

            $hash = hash('sha256', $pdf);

            $existingDocument = Document::where('hash', $hash)->first();

            if ($existingDocument) {
                $this->existingDocuments->put($file->name, $existingDocument);
                continue;
            }

            $documentName = (new Document())->makeNameUnique($file->name);

            Storage::disk('s3')->put("Inbox/$documentName", $pdf, [
                'visibility' => 'private',
                'ContentType' => $file->mime_type,
                'ContentDisposition' => 'inline',
            ]);

            Document::create([
                'name' => $documentName,
                'uploaded_by' => user()->id,
                'hash' => $hash
            ]);
        }

        $this->documents = [];

        $this->dispatch('refresh');
        $this->dispatch('flashNotification', message: __('Documents are uploaded'));
    }
}
