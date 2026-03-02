<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\LeaveTransaction;
use App\Models\User;
use App\Notifications\LeaveBalanceAdjusted;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class LeaveTransactionForm extends Modal
{
    use LogsActivity;

    public User|int $user;
    public int|float $amount;
    public string $comments;
    public mixed $transacted_on = null;
    public LeaveTransaction|int $leaveTransaction;

    public function mount(User $user, ?LeaveTransaction $leaveTransaction): void
    {
        $this->user = $user;
        $this->leaveTransaction = $leaveTransaction;

        if ($leaveTransaction->id) {
            $this->amount = $leaveTransaction->amount;
            $this->comments = $leaveTransaction->comments;
            $this->transacted_on = $leaveTransaction->transacted_on->date();
        }
    }

    public function render(): View
    {
        return view('livewire.forms.leave-transaction-form');
    }

    public function rules(): array
    {
        $rules = [
            'comments' => 'required',
            'transacted_on' => 'nullable|date',
            'amount' => ['required', 'numeric', 'not_in:0',],
        ];

        if ($this->leaveTransaction->id) {
            $rules['amount'][] = function ($attribute, $value, $fail) {
                if ($value === $this->leaveTransaction->amount) {
                    $fail('The amount must be changed');
                }
            };
        }

        return $rules;
    }

    public function submit(): void
    {
        $this->validate();

        $data = [
            'transacted_by' => auth()->id(),
            'transacted_on' => $this->transacted_on,
            'amount' =>  $this->amount,
            'comments' => $this->comments,
        ];

        if ($this->leaveTransaction->id){
            $data['amount'] = $this->amount - $this->leaveTransaction->amount;
        }

        $transaction = $this->user->createLeaveTransaction($data);
        $this->user->notify(new LeaveBalanceAdjusted($transaction));

        $this->close(andDispatch: [
            'flashNotification' => ['message' => __('Leave balance adjusted')]
        ]);
    }
}
