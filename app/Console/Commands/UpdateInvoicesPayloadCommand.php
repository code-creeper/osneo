<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Creditreform;
use Exception;
use Illuminate\Console\Command;
use Lexoffice;

class UpdateInvoicesPayloadCommand extends Command
{
    protected $signature = 'invoice:update-payload {--both}';

    protected $description = 'Update Invoice Payload for Lexoffice or Creditreform';

    public function handle(): void
    {
        $updatePayloadForBothServices = $this->option('both');

        if ($updatePayloadForBothServices) {
            $this->updateLexofficePayloads();
            $this->updateCreditreformPayloads();
            exit();
        }

        $service = $this->choice("Select service", ['Lexoffice', 'Creditreform']);
        $this->{"update{$service}Payloads"}();

    }

    private function updateLexofficePayloads(): void
    {
        $invoices = Invoice::whereNotNull('lexoffice_id')->get();

        foreach ($invoices as $invoice){
            try {
                Lexoffice::updateInvoicePayload($invoice);
            } catch (Exception $exception){
                $this->error("Error updating lexoffice payload for Invoice $invoice->id ". $exception->getMessage());
                continue;
            }

            $this->info("Lexoffice payload updated for Invoice $invoice->id");
        }
    }

    private function updateCreditreformPayloads(): void
    {
        $invoices = Invoice::whereNotNull('creditreform_id')->get();

        foreach ($invoices as $invoice) {
            try {
                Creditreform::updateInvoicePayload($invoice);
            } catch (Exception $exception) {
                $this->error("Error updating creditreform payload for Invoice $invoice->id ".$exception->getMessage());
                continue;
            }
            $this->info("Creditreform payload updated for Invoice $invoice->id");
        }
    }
}
