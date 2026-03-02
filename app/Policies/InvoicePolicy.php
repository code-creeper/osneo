<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {

    }

    public function view(User $user, Invoice $invoice)
    {
        if ($user->can('view invoices')){
            return true;
        }
    }

    public function create(User $user)
    {
    }

    public function update(User $user, Invoice $invoice)
    {
    }

    public function delete(User $user, Invoice $invoice)
    {
    }

    public function restore(User $user, Invoice $invoice)
    {
    }

    public function forceDelete(User $user, Invoice $invoice)
    {
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        if ($invoice->creditreform_payload->openGrossAmount == 0){
            return false;
        }

        return true;
    }

    public function export(User $user, Invoice $invoice): bool
    {
        if ($invoice->creditreform_id){
            return false;
        }

        if ($invoice->payment?->voucherStatus == 'paid'){
            return false;
        }

        return true;
    }

    public function writeOff(User $user, Invoice $invoice): bool
    {
        if ($invoice->creditreform_payload->openGrossAmount == 0){
            return false;
        }

        return true;
    }

    public function createDunningStop(User $user, Invoice $invoice): bool
    {
        if ($invoice->creditreform_payload->openGrossAmount == 0){
            return false;
        }

        return $invoice->creditreform_payload->dunningStopUntilDate == null;
    }

    public function deleteDunningStop(User $user, Invoice $invoice): bool
    {
        if ($invoice->creditreform_payload->openGrossAmount == 0){
            return false;
        }

        return $invoice->creditreform_payload->dunningStopUntilDate != null;
    }

    public function updateCreditreformPayload(User $user, Invoice $invoice): bool
    {
        if ( ! $invoice->creditreform_id) {
            return false;
        }

        return true;
    }

    public function updateLexofficePayload(User $user, Invoice $invoice): bool
    {
        if ( ! $invoice->lexoffice_id) {
            return false;
        }

        return true;
    }
}
