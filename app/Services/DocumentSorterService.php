<?php

namespace App\Services;

use App\Lexoffice\LexofficeApi;
use App\Models\Contract;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Invoice;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Log;

class DocumentSorterService
{
    public function sortVoucher(Document $document): void
    {
        if (!$document->lexoffice_id){
            return;
        }

        $voucher = LexofficeApi::get_voucher($document->lexoffice_id);
        $contact = LexofficeApi::get_contact($voucher->contactId);

        $invoiceTypes = ['salesinvoice', 'purchaseinvoice', 'salescreditnote', 'purchasecreditnote'];
        if ( ! in_array($voucher->type, $invoiceTypes)) {
            Log::channel('lexoffice')->error("Resource ID: $document->lexoffice_id \n Voucher is not an invoice");
            return;
        }

        $documentTypeKey = match ($voucher->type) {
            'salesinvoice', 'purchaseinvoice' => 'R',
            'salescreditnote' => 'STORNO',
            'purchasecreditnote' => 'GUS',
        };

        $documentType = DocumentType::with('properties')->where('key', $documentTypeKey)->first();

        $properties = $this->getPreparedProperties($documentType, [
            'kunden_lieferantenname' => $contact->company->name ?? "{$contact->person->firstName} {$contact->person->lastName}",
            'debitoren_kreditorennummer' => $contact->roles->{$document->source == 'KRE' ? 'vendor' : 'customer'}->number ?? '',
            'rechnungsnummer' => $voucher->voucherNumber,
            'vorgangsnummer' => str($voucher->remark ?? null)->take(17)->toString(),
            'rechnungssumme_brutto' => $voucher->totalGrossAmount,
            'umsatzsteuer' => (int) $voucher->voucherItems[0]->taxRatePercent,
            'falligkeit' => Carbon::parse($voucher->dueDate)->format('d.m.Y'),
        ]);

        $document->properties = $properties;
        $document->document_type_id = $documentType->id;
        $document->source = match ($voucher->type) {
            'purchaseinvoice', 'purchasecreditnote' => 'KRE',
            'salesinvoice', 'salescreditnote' => 'DEB',
        };

        $this->sortDocument($document, [
            'voucher' => $voucher,
            'contact' => $contact,
            'invoice_should_create' => $documentTypeKey == 'R' && $document->source == 'DEB',
            'invoice_type' => 'voucher'
        ]);
    }

    public function sortInvoice(Document $document, $isDownPaymentInvoice = false): void
    {
        if (!$document->lexoffice_id){
            return;
        }

        $endpoint = $isDownPaymentInvoice ? "get_down_payment_invoice" : "get_invoice";

        $voucher = LexofficeApi::{$endpoint}($document->lexoffice_id);
        $contact = LexofficeApi::get_contact($voucher->address->contactId);

        $documentTypeKey = 'R';
        $documentType = DocumentType::with('properties')->where('key', $documentTypeKey)->first();

        $properties = $this->getPreparedProperties($documentType, [
            'kunden_lieferantenname' => $contact->company->name ?? "{$contact->person->firstName} {$contact->person->lastName}",
            'debitoren_kreditorennummer' => $contact->roles->customer->number ?? '',
            'rechnungsnummer' => $voucher->voucherNumber,
            'vorgangsnummer' => str($voucher->remark ?? null)->take(17)->toString(),
            'rechnungssumme_brutto' => $voucher->totalPrice->totalGrossAmount,
            'umsatzsteuer' => isset($voucher->taxAmounts[0]) ? (int)$voucher->taxAmounts[0]->taxRatePercentage : null,
            'falligkeit' => Carbon::parse($voucher->dueDate)->format('d.m.Y'),
        ]);

        $document->document_type_id = $documentType->id;
        $document->source = 'DEB';
        $document->properties = $properties;

        $this->sortDocument($document, [
            'voucher' => $voucher,
            'contact' => $contact,
            'invoice_should_create' => true,
            'invoice_type' => 'invoice'
        ]);
    }

    public function sortQuotation(Document $document, Contract $contract): void
    {
        $documentTypeKey = 'ANG';
        $documentType = DocumentType::with('properties')->where('key', $documentTypeKey)->first();

        $quotation = $contract->getQuotation();

        $properties = $this->getPreparedProperties($documentType, [
            'kunden_lieferantenname' => $contract->customer?->name,
            'debitoren_kreditorennummer' => null,
            'angebotsnummer' => $quotation->voucherNumber,
            'vorgangsnummer' => $contract->ticket?->number,
            'rechnungssumme_brutto' => $quotation->totalPrice['totalGrossAmount'],
            'umsatzsteuer' => isset($quotation->taxAmounts[0]) ? (int) $quotation->taxAmounts[0]['taxRatePercentage'] : null,
        ]);

        $document->status = 2;
        $document->sorted_on = now();

        $document->document_type_id = $documentType->id;
        $document->source = 'DEB';
        $document->properties = $properties;
        $document->ticket_id = $contract->ticket_id;

        $document->generateName();
        $document->save();

        Storage::move($document->inboxPath(), $document->sorted_path);
    }

    private function sortDocument(Document $document, array $data): void
    {
        $voucher = $data['voucher'];
        $contact = $data['contact'];

        $document->status = 2;
        $document->sorted_on = now();

        $ticketNumber = str($voucher->remark ?? null)->take(17);

        if ($ticketNumber->doesntMatch('/^TKT-\d{6}-\d{6}$/')) {
            Log::channel('lexoffice')->error("Failed to sort Invoice $document->lexoffice_id \n The ticket number $ticketNumber is not valid.");

            return;
        }

        $ticket = Ticket::firstOrCreate(['number' => $ticketNumber]);

        $document->ticket_id = $ticket->id;

        $document->generateName();
        $document->save();

        Storage::move($document->inboxPath(), $document->sorted_path);

        if (config('lexoffice.debugging')){
            Log::channel('lexoffice')->debug("Document $document->lexoffice_id sorted successfully.");
        }

        $invoiceShouldCreate = $data['invoice_should_create'];

        if ( ! $invoiceShouldCreate) {
            return;
        }

        $payment = LexofficeApi::get_voucher_payments($document->lexoffice_id);
        Invoice::updateOrCreate(
            ['lexoffice_id' => $document->lexoffice_id,],
            [
                'document_id' => $document->id,
                'type' => $data['invoice_type'],
                'lexoffice_payload' => [
                    'voucher' => $voucher,
                    'contact' => $contact,
                    'payment' => $payment,
                ],
            ]
        );
    }

    private function getPreparedProperties(DocumentType $documentType, array $propertiesValues): array
    {
        $properties = [];

        foreach ($propertiesValues as $propertyName => $value){
            $property = $documentType->properties->where('key', "{$documentType->id}_$propertyName")->first();
            $properties[$property->id] = array(
                'name' => $property->name,
                'value' => $value,
            );
        }

        return $properties;
    }
}
