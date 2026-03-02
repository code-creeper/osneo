<?php

namespace App\Console\Commands;

use App\Lexoffice\LexofficeEventHandler;
use App\Models\Document;
use App\Models\DocumentType;
use Illuminate\Console\Command;
use LexofficeApi;

class SyncInvoicesCommand extends Command
{
    protected $signature = 'invoice:sync';

    protected $description = 'Create all missing invoices and vouchers';

    public function handle(LexofficeEventHandler $lexofficeEventHandler): void
    {
        $this->createInvoices($lexofficeEventHandler);
    }

    private function createInvoices(LexofficeEventHandler $lexofficeEventHandler): void
    {
        $invoices = LexofficeApi::get_vouchers('invoice,downpaymentinvoice', 'open');

        foreach ($invoices as $invoice) {
            $invoiceId = $invoice->id;

            $this->info("Syncing $invoice->id");
            $isDownPaymentInvoice = $invoice->voucherType == 'downpaymentinvoice';

            try {
                $lexofficeEventHandler->invoiceUpdatedOrCreated($invoiceId, $isDownPaymentInvoice);
            } catch (\Exception $exception) {
                \Log::channel('creditreform')->error(
                    "Error while syncing invoices Lexoffice ID: $invoiceId \n".
                    $exception->getMessage()
                );

                continue;
            }
        }
    }

    private function createVouchers(): void
    {
        $documentTypeId = DocumentType::where('key', 'R')->value('id');

        $documents = Document::query()
            ->where('document_type_id', $documentTypeId)
            ->where('source', 'DEB')
            ->whereNotNull('lexoffice_id')
            ->whereDoesntHave('invoice')
            ->cursor();

        foreach ($documents as $document){
            try {
                $voucher = LexofficeApi::get_voucher($document->lexoffice_id);
                $contact = LexofficeApi::get_contact($voucher->contactId);
                $payment = LexofficeApi::get_voucher_payments($document->lexoffice_id);

                $document->invoice()->create([
                    'lexoffice_id' => $document->lexoffice_id,
                    'lexoffice_payload' => [
                        'voucher' => $voucher,
                        'contact' => $contact,
                        'payment' => $payment,
                    ],
                ]);
            } catch (\Exception $exception){
                \Log::channel('creditreform')->error(
                    "Error while syncing invoices Document ID: $document->id | Lexoffice ID: $document->lexoffice_id \n".
                    $exception->getMessage()
                );

                continue;
            }
        }
    }
}
