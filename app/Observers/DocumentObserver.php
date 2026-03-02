<?php

namespace App\Observers;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentObserver
{
    public function updated(Document $document): void
    {
        // assign document to all subscribers of this document type!
        if ($document->documentType?->subscriber_ids) {
            $document->users()->syncWithoutDetaching($document->documentType->subscriber_ids);
        }
    }

    public function deleting(Document $document): void
    {
        // store the trash path before restoring for later use
        $trashPath = $document->trashPath();

        if ($document->status == 0) {
            $document->update([
                'name' => $document->makeNameUnique($document->name),
            ]);
        } else {
            $document->update([
                'sorted_path' => $document->makeNameUnique($document->sorted_path, 'sort'),
            ]);
        }

        Storage::move($trashPath, $document->pdf_path);
    }

    public function restored(Document $document): void
    {
        if($document->isForceDeleting()){
            Storage::delete($document->pdf_path);
        } else {
            Storage::move($document->pdf_path, $document->trashPath());
        }
    }
}
