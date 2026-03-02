<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\Invoice;
use App\Traits\HasWireElementsPlaceholder;
use Creditreform;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use WireElements\Pro\Components\Modal\Modal;

#[Lazy]
class DunningStopForm extends Modal
{
    use LogsActivity;
    use HasWireElementsPlaceholder;

    public Invoice|int $invoice;
    public string $date;

    public string $heading;

    protected function rules(): array
    {
        return [
            'date' => 'required|date'
        ];
    }

    public function mount(Invoice $invoice): void
    {
        $this->heading = __('Create Dunning Stop');
        $this->invoice = $invoice;
    }

    public function render(): View
    {
        $data = array();

        return view('livewire.forms.dunning-stop-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        try {
            Creditreform::createDunningStop($this->invoice, $this->date);
        } catch (\Exception $exception){
            $this->dispatch('flashNotification', message: $exception->getMessage(), type: 'error');
            return;
        }

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('Dunning stop created')]
        ]);
    }
}
