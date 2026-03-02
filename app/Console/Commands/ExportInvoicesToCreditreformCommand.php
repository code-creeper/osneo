<?php

namespace App\Console\Commands;

use App\Jobs\SendInvoiceToCreditReformJob;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class ExportInvoicesToCreditreformCommand extends Command
{
    protected $signature = 'invoice:export-to-creditreform';

    protected $description = 'Export invoices to creditreform';

    public function handle(): void
    {
        $invoices = Invoice::query()
            ->whereJsonContains('lexoffice_payload->voucher->voucherStatus', "open")
            ->whereNull('creditreform_id')
            ->get();

        foreach ($invoices as $invoice){
            SendInvoiceToCreditReformJob::dispatch($invoice);
        }
    }
}
