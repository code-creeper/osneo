<?php

namespace App\Livewire\Modals;

use App\Livewire\Traits\LogsActivity;
use App\Models\DocumentType;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\SlideOver\SlideOver;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class DocumentProperties extends SlideOver
{
    use LogsActivity;

    use InteractsWithConfirmationModal;

    public DocumentType|int $documentType;

    public function mount(DocumentType $documentType): void
    {
        $this->documentType = $documentType;
    }

    public function render(): View
    {
        $data = array();

        return view('livewire.modals.document-properties', $data);
    }

    public static function attributes(): array
    {
        return [
            'size' => '5xl',
        ];
    }

    public static function behavior(): array
    {
        return [
            'close-on-escape' => true,
            'close-on-backdrop-click' => true,
        ];
    }
}
