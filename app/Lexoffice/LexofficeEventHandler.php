<?php

namespace App\Lexoffice;

use App\Models\Document;
use App\Models\Invoice;
use Creditreform;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Log;
use DocumentSorter;

class LexofficeEventHandler
{
    private string $downloadsDir = 'downloads';
    private string $downloadsPath;

    public function __construct()
    {
        $this->downloadsPath =  public_path("storage/$this->downloadsDir");

        if (!File::exists($this->downloadsPath)) {
            File::makeDirectory($this->downloadsPath);
        }
    }

    public function voucherUpdatedOrCreated($resourceId): void
    {
        try {
            $files = LexofficeApi::get_voucher_files($resourceId, "$this->downloadsPath/{$resourceId}_");
        } catch (Exception $exception) {
            Log::channel('lexoffice')->error("Resource ID: $resourceId \n Error in retrieving files \n ".$exception->getMessage());

            return;
        }

        foreach ($files as $file) {
            $document = $this->updateOrCreateDocument($file, $resourceId);

            try {
                DocumentSorter::sortVoucher($document);
            } catch (Exception $exception) {
                Log::channel('lexoffice')->error("Failed to sort Voucher $resourceId \n ".$exception->getMessage());
            }
        }
    }

    public function paymentChanged($resourceId): void
    {
        $invoice = Invoice::where('lexoffice_id', $resourceId)->firstOrFail();

        Creditreform::recordPayment($invoice);
    }

    public function invoiceUpdatedOrCreated($resourceId, $isDownPaymentInvoice = false): void
    {
        $endpoint = $isDownPaymentInvoice ? "get_down_payment_invoice" : "get_invoice";

        $invoice = LexofficeApi::{$endpoint}($resourceId);

        if ($invoice->voucherStatus == 'draft'){
            return;
        }

        try {
            $fileName = "$this->downloadsPath/$resourceId.pdf";
            LexofficeApi::save_pdf_by_uuid($invoice->files->documentFileId, $fileName);
        } catch (Exception $exception) {
            Log::channel('lexoffice')->error(
                "Resource ID: $resourceId
                \n Error in retrieving files
                \n ".$exception->getMessage()
            );

            return;
        }

        $document = $this->updateOrCreateDocument($fileName, $resourceId);

        try {
            DocumentSorter::sortInvoice($document, $isDownPaymentInvoice);
        } catch (Exception $exception) {
            Log::channel('lexoffice')->error(
                "Failed to sort Document $resourceId
                \n ".$exception->getMessage()
            );
        }
    }

    public function voucherOrInvoiceDeleted($resourceId): void
    {
        Document::where('lexoffice_id', $resourceId)->update(['lexoffice_id' => null]);

        $invoice = Invoice::where('lexoffice_id', $resourceId)->first();

        if ( ! $invoice) {
            return;
        }

        try {
            Creditreform::cancelInvoice($invoice);
        } catch (Exception $exception) {
            Log::channel('creditreform')->error("Failed to cancel invoice $resourceId \n".$exception->getMessage());
        }

        $invoice->delete();
    }

    private function updateOrCreateDocument($file, $lexofficeId): Document
    {
        $fileName = str($file)->afterLast('/');
        $filePath = "$this->downloadsPath/$fileName";

        $document = Document::updateOrCreate(
            [
                'name' => $fileName,
                'lexoffice_id' => $lexofficeId,
            ],
            ['status' => 0,]
        );

        // delete old file if exists
        if ($document->sorted_path) {
            Storage::delete($document->sorted_path);
            $document->update([
                'sorted_path' => null,
            ]);
        }

        Storage::disk('s3')->put("Inbox/$fileName", File::get($filePath), [
            'visibility' => 'private',
            'ContentType' => 'application/pdf',
            'ContentDisposition' => 'inline',
        ]);

        File::delete($filePath);

        return $document;
    }
}
