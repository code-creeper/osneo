<?php

namespace App\Livewire\Forms;

use App\Enums\DocumentPropertyType;
use App\Livewire\Traits\LogsActivity;
use App\Models\DocumentProperty;
use App\Models\DocumentType;
use App\Models\User;
use Arr;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class DocumentPropertyForm extends Modal
{
    use LogsActivity;

    public DocumentProperty|int $documentProperty;
    public DocumentType|int|null $documentType;

    public string $heading;
    public bool $editing = false;

    public $subscriberIds = [];

    protected function rules(): array
    {
        return [
            'documentProperty.document_type_id' => 'required',
            'documentProperty.key' => 'required',
            'documentProperty.name' => 'required',
            'documentProperty.order' => 'required',
            'documentProperty.type' => 'required',
            'documentProperty.active' => 'required',
            'documentProperty.is_name' => 'required',
            'documentProperty.rules' => 'nullable',
        ];
    }

    public function mount(DocumentType $documentType, DocumentProperty $documentProperty): void
    {
        $this->documentProperty = $documentProperty;

        $this->documentType = $documentType->id ? $documentType : $documentProperty->documentType;

        $this->heading = __(':type > Create Document Property', ['type' => $this->documentType->name]);

        if ($this->documentProperty->id){
            $this->editing = true;
            $this->heading = __('Edit Document Property');
        } else {
            $this->documentProperty->document_type_id = $documentType->id;
        }
    }

    public function render(): View
    {
        $data = array();

        $data['propertyTypes'] = DocumentPropertyType::toArray();

        return view('livewire.forms.document-property-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->documentProperty->save();

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('Document Property Updated')]
        ]);
    }

    public static function attributes(): array
    {
        return [
            'size' => '5xl'
        ];
    }
}
