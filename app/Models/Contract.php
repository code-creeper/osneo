<?php

namespace App\Models;

use App\Helpers\GeneralHelper;
use App\Services\DocumentSorterService;
use File;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use LexofficeApi;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

class Contract extends Model
{
    use HasFactory;

    use SchemalessAttributesTrait;
    use HasJsonRelationships;

    protected $guarded = ['id'];

    protected $schemalessAttributes = [
        'services',
        'sections',
        'lexoffice_payload',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::created(function (self $contract) {
            $contract->sendToLexOffice();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeWithServices(): Builder
    {
        return $this->services->modelScope();
    }

    public function scopeWithSections(): Builder
    {
        return $this->sections->modelScope();
    }

    public function scopeWithLexofficePayload(): Builder
    {
        return $this->lexoffice_payload->modelScope();
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function getAmount(): float
    {
        return (float)collect($this->services)->sum('price');
    }

    public function prepareContractDataForLexoffice(): array
    {
        $data = array();

        $data['voucherDate'] = $this->created_at->format('Y-m-d\TH:i:s.vP');
        $data['expirationDate'] = now()->addWeeks(4)->format('Y-m-d\TH:i:s.vP');

        $billingAddress = $this->customer->billingAddress;

        if ($this->customer->manager_id){
            $billingAddress = $this->customer->manager->billingAddress;
        }

        $data['address'] = [
            'contactId' => null,
            'name' => $this->customer->name,
            'supplement' => $this->customer->manager_id ? "vertr. durch {$this->customer->manager?->name}" : "",
            'street' => $billingAddress->street,
            'city' => $billingAddress->city,
            'zip' => $billingAddress->zip_code,
            'countryCode' => "DE",
        ];

        $items = [];

        if($this->ticket_id){
            $items[] =  [
                "type" => "text",
                "name" => "Vorgangsnummer: {$this->ticket->number}",
            ];
        }

        $services = $this->services;

        foreach ($services as $service){
            $serviceName = isset($service['category_name']) ? "{$service['category_name']} - " : '';
            $serviceName .= $service['service_name'];

            $item = [
                "type" => "custom",
                "name" => "$serviceName - {$service['size']} {$service['unit']}",
                "description" => $service['description'] ?? null,
                "quantity" => 1,
                "unitName" => "Piece",
                "unitPrice" => [
                    "currency" => "EUR",
                    "netAmount" => $service['price'],
                    "taxRatePercentage" => 19,
                ],
            ];

            $items[] = $item;
        }

        $data['lineItems'] = $items;

        $data['totalPrice'] = ['currency' => 'EUR'];
        $data['taxConditions'] = ['taxType' => 'net'];

        $data['paymentConditions'] = [
            'paymentTermLabel' => $this->sections->payment_terms['label'] ?? '',
            'paymentTermDuration' => $this->sections->payment_terms['duration'] ?? 7,
        ];

        $data['introduction'] = $this->sections->introduction;
        $data['remark'] = $this->sections->remarks;
        $data['title'] = $this->sections->title;

        return $data;
    }

    public function sendToLexOffice(): void
    {
        // if we have the lexoffice_id then it's already synced!
        if ($this->lexoffice_id) {
            return;
        }

        $data = $this->prepareContractDataForLexoffice();

        $response = LexofficeApi::create_quotation($data, true);

        if ($response?->id) {
            $payload = LexofficeApi::get_quotation($response->id);

            $this->update([
                'lexoffice_id' => $response->id,
                'lexoffice_payload' => $payload,
            ]);
        }
    }

    public function getQuotation(): mixed
    {
        if ( ! $this->lexoffice_id) {
            return null;
        }

        if ($this->lexoffice_payload->id) {
            return $this->lexoffice_payload;
        }

        $quotation = LexofficeApi::get_quotation($this->lexoffice_id);

        $this->update([
            'lexoffice_payload' => $quotation,
        ]);

        return $quotation;
    }

    public function getDocumentName(): string
    {
        $quotation = $this->getQuotation();
        return GeneralHelper::objectToArray($quotation->files)['documentFileId'].".pdf";
    }

    public function downloadDocumentToInbox($documentName): bool
    {
        $filename = GeneralHelper::getDownloadsPath("$this->lexoffice_id.pdf");
        $fileDownloaded = LexofficeApi::get_pdf('quotations', $this->lexoffice_id, $filename);

        if ( ! $fileDownloaded) {
            return false;
        }

        $file = File::get($filename);

        $uploadSuccessful = Storage::disk('s3')->put(Document::$inboxDir."/$documentName", $file, [
            'visibility' => 'private',
            'ContentType' => 'application/pdf',
            'ContentDisposition' => 'inline',
        ]);

        if ( ! $uploadSuccessful) {
            return false;
        }

        return true;
    }

    // if document already exist, we return the document;
    // else we create and return the document
    public function getDocument(): false|Document
    {
        $documentName = $this->getDocumentName();

        $existingDocument = $this->documents()->where('name', $documentName)->first();
        if ($existingDocument) {
            return $existingDocument;
        }

        // we first download the file to Inbox, and then create document for that
        if ( ! $this->downloadDocumentToInbox($documentName)) {
            return false;
        }

        $document = $this->documents()->create([
            'name' => $documentName,
        ]);

        app(DocumentSorterService::class)->sortQuotation($document, $this);

        return $document;
    }
}
