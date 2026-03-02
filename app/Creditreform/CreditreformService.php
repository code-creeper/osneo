<?php

namespace App\Creditreform;

use App\Models\Invoice;
use Exception;
use Illuminate\Support\Arr;
use Lexoffice;
use LexofficeApi;
use CreditreformApi;
use Log;

class CreditreformService
{

    /**
     * @throws Exception
     */
    public function exportInvoice(Invoice $invoice): void
    {
        if ( ! $invoice->lexoffice_id) {
            throw new Exception("Failed to export Invoice $invoice->id \n Lexoffice ID not found");
        }

        $voucher = Lexoffice::getVoucher($invoice);
        $contact = Lexoffice::getContact($invoice, $voucher);
        $billingAddress = $contact->addresses->billing[0];

        $contactIsPerson = property_exists($contact, 'person');

        if ($contactIsPerson) {
            $contactPerson = $contact->person;
        } else {
            $contactPerson = property_exists($contact->company, 'contactPersons')
                ? (object) Arr::where($contact->company->contactPersons, fn(object $person) => $person->primary == true)[0] ?? null
                : null;
        }

        $email = $contactIsPerson ? Arr::first($contact->emailAddresses ?? null) : $contactPerson->emailAddress ?? null;

        $name = $contactPerson->lastName ?? null;
        if ( ! $contactIsPerson && ! $contactPerson?->firstName) {
            $name = $contact->company->name;
        }

        $totalGrossAmount = $invoice->type == 'voucher' ? $voucher->totalGrossAmount : $voucher->totalPrice->totalGrossAmount;
        $totalTaxAmount = $invoice->type == 'voucher' ? $voucher->totalTaxAmount : $voucher->totalPrice->totalTaxAmount;

        $data = [
            'invoiceDate' => $voucher->voucherDate,
            'dueDate' => $voucher->dueDate,
            'invoiceNo' => $voucher->voucherNumber,
            'orderNo' => $voucher->remark ?? null,
            'orderDate' => $voucher->shippingDate ?? null,
            'gender' => ! $contactIsPerson ? 'Company' : $this->getGender($contactPerson->salutation),
            'firstName' => $contactPerson->firstName ?? null,
            'name' => $name,
            'emailAddress' => $email ?? null,
            'address' => [
                'street' => $billingAddress->street ?? null,
                'country' => $billingAddress->countryCode ?? null,
                'postalCode' => $billingAddress->zip ?? null,
                'city' => $billingAddress->city ?? null,
                'annex' => null,
            ],
            'netAmount' => ($totalGrossAmount - $totalTaxAmount),
            'grossAmount' => $totalGrossAmount,
            'overrideExistingDebtorData' => true,
        ];

        // get file from lexoffice, and set it to the invoice data
        if (property_exists($voucher, 'files')) {
            $fileId = $invoice->isVoucher() ? $voucher->files[0] : $voucher->files->documentFileId;
            $request = LexofficeApi::get_pdf_file($fileId);
            $data['documentBytes'] = base64_encode(substr($request['body'], $request['header']['header_size']));
            $data['documentType'] = match ($request['header']['content_type']) {
                'image/png' => 'PNG',
                'image/jpg', 'image/jpeg' => 'JPG',
                'application/pdf' => 'PDF',
            };
        }

        $invoiceId = CreditreformApi::createStructuredInvoice($data);

        $invoice->update([
            'creditreform_id' => $invoiceId,
        ]);

        $this->updateInvoicePayload($invoice);

        // we sync the payment initially, after exporting invoice to creditreform
        // in case there are payments made already!
        $this->recordPayment($invoice);
    }

    /**
     * @throws Exception
     */
    public function recordPayment(Invoice $invoice): void
    {
        $voucher = Lexoffice::getVoucher($invoice);

        if ( ! $voucher) {
            return;
        }

        if ($voucher->voucherStatus == 'open' && ! $invoice->creditreform_id) {
            $this->exportInvoice($invoice);

            // after exporting the invoice, we then check the payment anyway,
            // so we do a return and stop further execution here.
            return;
        }

        if ( ! $invoice->creditreform_id) {
            Log::channel("creditreform")->error("Failed to record payment for Invoice $invoice->id \n Creditreform ID not found");

            return;
        }

        $oldPayment = (object) $invoice->lexoffice_payload->payment;
        $payment = LexofficeApi::get_voucher_payments($invoice->lexoffice_id);
        $amount = $oldPayment->openAmount - $payment->openAmount;

        if ($amount == 0) {
            return;
        }

        $data = [
            'amount' => $amount,
            'date' => today()->toDateString(),
            'assignedInvoiceId' => $invoice->creditreform_id,
        ];

        CreditreformApi::createPayment($data);

        $invoice->lexoffice_payload->payment = $payment;
        $invoice->save();

        $this->updateInvoicePayload($invoice);

        Log::channel('creditreform')->info("Payment of $amount recorded for invoice id $invoice->creditreform_id");
    }

    /**
     * @throws Exception
     */
    public function updateInvoicePayload(Invoice $invoice): void
    {
        $invoicePayload = CreditreformApi::getInvoice($invoice->creditreform_id);

        $invoice->update([
            'creditreform_payload' => $invoicePayload,
        ]);
    }

    /**
     * @throws Exception
     */
    public function deleteDunningStop(Invoice $invoice): void
    {
        $payload = CreditreformApi::deleteDunningStop($invoice->creditreform_id);

        $invoice->update([
            'creditreform_payload' => $payload,
        ]);
    }

    /**
     * @throws Exception
     */
    public function createDunningStop(Invoice $invoice, string $date): void
    {
        $payload = CreditreformApi::createDunningStop($invoice->creditreform_id, $date);

        $invoice->update([
            'creditreform_payload' => $payload,
        ]);
    }

    /**
     * @throws Exception
     */
    public function writeOffInvoice(Invoice $invoice): void
    {
        $payload = CreditreformApi::writeOffInvoice($invoice->creditreform_id);

        $invoice->update([
            'creditreform_payload' => $payload,
        ]);
    }

    /**
     * @throws Exception
     */
    public function cancelInvoice(Invoice $invoice): void
    {
        if ( ! $invoice->creditreform_id) {
            return;
        }

        $payload = CreditreformApi::cancelInvoice($invoice->creditreform_id);

        $invoice->update([
            'creditreform_payload' => $payload,
        ]);
    }

    /**
     * @throws Exception
     */
    public function refundPayment(Invoice $invoice): void
    {
        $data = [
            'amount' => 1,
            'date' => today(),
            'assignedInvoiceId' => $invoice->creditreform_id,
        ];

        $payload = CreditreformApi::refundPayment($data);
        /*$invoice->update([
            'creditreform_payload' => $payload,
        ]);*/
    }


    private function getGender(string $salutation = null): ?string
    {
        if ( ! $salutation) {
            return null;
        }

        return match (strtolower($salutation)) {
            'frau' => 'Female',
            'herr' => 'Male',
            default => null,
        };
    }
}
