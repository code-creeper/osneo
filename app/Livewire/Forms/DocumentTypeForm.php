<?php

namespace App\Livewire\Forms;

use App\Helpers\GeneralHelper;
use App\Livewire\Traits\LogsActivity;
use App\Models\DocumentType;
use App\Models\User;
use App\Rules\RequiredForCurrentLocaleRule;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class DocumentTypeForm extends Modal
{
    use LogsActivity;

    public DocumentType|int $documentType;

    public string $title;
    public bool $editing = false;

    public array $subscriberIds = [];
    public array $name;

    protected function rules(): array
    {
        return array_merge(
            GeneralHelper::translateableFieldRules('name', [
                new RequiredForCurrentLocaleRule(), 'string', 'max:255',
            ]), [
            'documentType.key' => 'required',
            'subscriberIds' => 'nullable|array',
            'documentType.lexoffice' => 'nullable',
        ]);
    }

    public function mount(DocumentType $documentType): void
    {
        $this->title = __('Create Document Type');
        $this->documentType = $documentType;

        $this->name = $documentType->getTranslations('name');

        $this->documentType->lexoffice = (int)$this->documentType->lexoffice;

        if ($this->documentType->id){
            $this->editing = true;
            $this->title = __('Edit Document Type');

            $this->subscriberIds = $this->documentType->subscriber_ids ?? [];
        }
    }

    public function render(): View
    {
        $data = array();

        $data['users'] = User::relevant()->oldest('first_name')->get()->toKeyValuePair();

        return view('livewire.forms.document-type-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->documentType->name = $this->name;
        $this->documentType->subscriber_ids = $this->subscriberIds;

        $this->documentType->save();

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('Document Type Updated')]
        ]);
    }

    public static function attributes(): array
    {
        return [
            'size' => '5xl'
        ];
    }
}
