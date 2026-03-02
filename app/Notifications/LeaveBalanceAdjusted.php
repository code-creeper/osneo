<?php

namespace App\Notifications;

use App\Models\LeaveTransaction;
use Illuminate\Notifications\Notification;

class LeaveBalanceAdjusted extends Notification
{

    private LeaveTransaction $transaction;

    public function __construct(LeaveTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function via(): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'message' => $this->getMessage(),
            'icon' => 'fal fa-bell'
        ];
    }

    public function getMessage(): string
    {
        $action = $this->transaction->isDebit() ? __('Debited') : __('Credited');
        return __(
            'An admin has <strong>:action</strong> :amount leaves to your leave balance',
            [
                'action' => $action,
                'amount' => $this->transaction->amount
            ]
        );
    }
}
