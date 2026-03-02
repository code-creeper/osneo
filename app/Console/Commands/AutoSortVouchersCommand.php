<?php

namespace App\Console\Commands;

use App\Models\Document;
use DocumentSorter;
use Exception;
use Illuminate\Console\Command;
use Log;

class AutoSortVouchersCommand extends Command
{
    protected $signature = 'lexoffice:sort-vouchers';

    protected $description = 'Automatically sort vouchers from lexoffice';

    public function handle(): void
    {
        $documents = Document::whereIn('status', [0, 1])->whereNotNull('lexoffice_id')->get();

        foreach ($documents as $document) {
            //todo:: if voucher type is not specified, get voucher type from lexoffice
            /*if($document->lexoffice_voucher_type){
                $vouchers = Lexoffice::get_vouchers('invoice,downpaymentinvoice', 'open');
            }*/

            try {
                if ($document->lexoffice_voucher_type == 'voucher') {
                    DocumentSorter::sortVoucher($document);
                } elseif (in_array($document->lexoffice_voucher_type, ['downpaymentinvoice', 'invoice'])) {
                    $isDownPaymentInvoice = $document->lexoffice_voucher_type == 'downpaymentinvoice';
                    DocumentSorter::sortInvoice($document, $isDownPaymentInvoice);
                } else {
                    Log::channel('lexoffice')->error("Failed to sort document with lexoffice ID $document->lexoffice_id \n Unknown voucher type");
                }
            } catch (Exception $exception) {
                Log::channel('lexoffice')->error("Failed to sort document with lexoffice ID $document->lexoffice_id \n ".$exception->getMessage());
            }
        }
    }
}
