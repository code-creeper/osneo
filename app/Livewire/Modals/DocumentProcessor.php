<?php

namespace App\Livewire\Modals;

use App\Enums\ContactType;
use App\Enums\DocumentPropertyType;
use App\Livewire\Traits\LogsActivity;
use App\Models\Contact;
use App\Models\Document;
use App\Models\DocumentProperty;
use App\Models\DocumentType;
use App\Models\Ticket;
use App\Traits\TrimAndNullEmptyStrings;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Log;
use WireElements\Pro\Components\Modal\Modal;

class DocumentProcessor extends Modal
{
    use LogsActivity;

    use TrimAndNullEmptyStrings;

    public int|Document $document;

    public ?string $documentType = null;
    public ?string $source = null;
    public ?int $docTypeId = null;
    public Collection $documentProperties;
    public Collection $documentTypes;

    public array $properties = [];

    public function mount(Document $document): void
    {
        $this->document = $document;
        $this->documentTypes = DocumentType::forLexOffice($document->isLexOfficeFile())->oldest('name')->get();
        $this->source = $this->document->source;
        $this->docTypeId = $this->document->document_type_id;
        $this->documentType = $this->documentTypes->where('id', $this->docTypeId)?->value('key');
        $this->updatedDocumentType();
    }

    public function render(): View
    {
        $data = array();

        $data['document_sources'] = ContactType::cases();
        $data['title'] = __('Sort the document');

        return view('livewire.modals.document-processor', $data);
    }

    public function updatedProperties($value, $key): void
    {
        $contactNameField = $this->documentProperties->where('type', DocumentPropertyType::CONTACT_NAME)->value('name');
        $contactNumberField = $this->documentProperties->where('type', DocumentPropertyType::CONTACT_NUMBER)->value('name');

        if ($key == $contactNameField){
            $contactName = $value;
            $number = Contact::where('name', $contactName)->first()?->customer->number;

            if($number){
                $this->properties[$contactNumberField] = $number;
            }
        }
    }

    public function updatedDocumentType(): void
    {
        $document = $this->document;
        $this->docTypeId = $this->documentTypes->where('key', $this->documentType)?->value('id');
        $this->documentProperties = DocumentProperty::where('document_type_id', $this->docTypeId)
            ->where('active', 1)
            ->oldest('order')
            ->get();

        $properties = [];
        foreach ($this->documentProperties as $property) {
            $properties[$property->name] = $document->properties->get($property->id)['value'] ?? '';
        }

        $this->properties = $properties;
    }

    public function submit(): void
    {
        $this->validateData();
        $document = $this->document;

        // if it's already sorted, then we are editing it
        $editing = (bool)$document->sorted_on;

        $ticketNumber = null;

        foreach ($this->documentProperties as $property) {
            // if property is ticket number, we make sure to only take the first 17 characters
            if ($property->type == DocumentPropertyType::Ticket){
                $ticketNumber = str($this->properties[$property->name])->take(17)->toString();
                $this->properties[$property->name] = $ticketNumber;
            }

            $document->properties->{$property->id} = [
                'name' => $property->name,
                'value' => $this->properties[$property->name],
            ];
        }

        // update sorted_on only when sorting for the first time
        if (! $editing){
            $document->sorted_on = now();
        }

        $document->status = 2;
        $document->sorted_by = auth()->id();
        $document->source = $this->source;
        $document->document_type_id = $this->docTypeId;

        $document->save();

        // get the path before generating the name, after name is generated, the path will be updated
        $oldPath = $editing ? $document->sorted_path : $document->inboxPath();

        $document->generateName();

        try {
            Storage::move($oldPath, $document->sorted_path);
        } catch (Exception $exception){
            Log::error(
                "Failed to sort document. Document ID: $document->id \n".
                "Can not move document from $oldPath to $document->sorted_path \n".
                $exception->getMessage()
            );

            $this->close(andEmit: [
                'flashNotification' => ['message' => __('Failed to sort document'), 'type' => 'error']
            ]);

            return;
        }

        $this->dispatch('flashNotification', message: __('Document successfully sorted'));
        $this->dispatch('refresh');

        if ($ticketNumber) {
            $this->createTicket($ticketNumber);
        } else {
            $this->close();
        }
    }

    public function createTicket(string $ticketNumber): void
    {
        if (str($ticketNumber)->doesntMatch('/^TKT-\d{6}-\d{6}$/')) {
            $this->dispatch(
                'flashNotification',
                message: __("The ticket number $ticketNumber is not valid."),
                type: 'warning'
            );
            $this->close();

            return;
        }

        $ticket = Ticket::firstOrCreate(['number' => $ticketNumber]);

        $this->document->update([
            'ticket_id' => $ticket->id
        ]);

        if ( ! $ticket->synced) {
            $this->dispatch('modal.open', component: 'ticket-created-alert', arguments: ['ticket' => $ticket->id]);
        } else {
            $this->close();
        }
    }

    public function validateData(): void
    {
        $rules = [
            'documentType' => 'required',
            'source' => 'required',
        ];

        foreach ($this->documentProperties as $property) {
            if ($property->rules) {
                $rules["properties.$property->name"] = $property->rules;
            } else {
                $rules["properties.$property->name"] = 'nullable';
            }
        }

        $this->validate($rules);
    }

    public static function attributes(): array
    {
        return [
            'size' => '7xl',
        ];
    }
}
