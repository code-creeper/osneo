<?php

namespace App\Livewire\Modals;

use App\Livewire\Traits\LogsActivity;
use App\Models\Document;
use App\Traits\HasWireElementsPlaceholder;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use WireElements\Pro\Components\Modal\Modal;

#[Lazy]
class DocumentViewer extends Modal
{
    use LogsActivity;

    use HasWireElementsPlaceholder;

    public int $documentId;
    public int|Document $document;

    public bool $forceClose = false;

    public function mount(Document $document, bool $forceClose = false): void
    {
        $this->forceClose = $forceClose;
        $this->document = $document;
    }

    public function render(): View
    {
        if ( ! $this->document->getUrl()) {
            $this->dispatch('flashNotification', message: __('Failed to open the document'), type: 'error');
        }

        return view('livewire.modals.document-viewer');
    }

    public static function attributes() : array
    {
        return [
            'size' => '5xl'
        ];
    }
}
