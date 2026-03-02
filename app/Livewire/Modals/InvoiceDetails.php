<?php

namespace App\Livewire\Modals;

use App\Livewire\Traits\LogsActivity;
use App\Models\Invoice;
use App\Traits\HasWireElementsPlaceholder;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use WireElements\Pro\Components\Modal\Modal;

#[Lazy]
class InvoiceDetails extends Modal
{
    use LogsActivity;

    use HasWireElementsPlaceholder;
    public Invoice|int $invoice;

    public function placeholderConfig(): array|string
    {
        return "line:classes=w-75|block:ct=mt-4,cb=mb-4,size=5xl|button:classes=mb-0";
    }

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    public function render(): View
    {
        return view('livewire.modals.invoice-details');
    }

    public static function attributes(): array
    {
        return [
            'size' => '5xl',
        ];
    }
}
