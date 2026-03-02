<?php

namespace App\Lexoffice;

use App\Helpers\GeneralHelper;
use App\Models\Invoice;
use Exception;

class LexofficeService
{
    public function getVoucher(Invoice $invoice): object|null
    {
        if ($invoice->type == 'invoice'){
            $voucher = LexofficeApi::get_invoice($invoice->lexoffice_id);
        } elseif ($invoice->type == 'downpaymentinvoice'){
            $voucher = LexofficeApi::get_down_payment_invoice($invoice->lexoffice_id);
        } else {
            $voucher = LexofficeApi::get_voucher($invoice->lexoffice_id);
        }

        return $voucher;
    }

    public function getContact(Invoice $invoice, $voucher = null): object|null
    {
        $voucher = $voucher ?: $invoice->voucher;
        $contactId = $invoice->type == 'voucher' ? $voucher->contactId : GeneralHelper::objectToArray($voucher->address)['contactId'];

        return LexofficeApi::get_contact($contactId);
    }

    /**
     * @throws Exception
     */
    public function updateInvoicePayload(Invoice $invoice): void
    {
        $voucher = $this->getVoucher($invoice);

        if ( ! $voucher) {
            throw new Exception("$invoice->type not found. Lexoffice ID: $invoice->lexoffice_id");
        }

        $contact = $this->getContact($invoice, $voucher);
        $payment = LexofficeApi::get_voucher_payments($invoice->lexoffice_id);

        $invoice->lexoffice_payload->voucher = $voucher;
        $invoice->lexoffice_payload->contact = $contact;
        $invoice->lexoffice_payload->payment = $payment;

        $invoice->save();
    }
}
