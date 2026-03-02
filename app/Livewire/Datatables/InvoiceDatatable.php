<?php

namespace App\Livewire\Datatables;

use App\Jobs\SendInvoiceToCreditReformJob;
use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\Invoice;
use Creditreform;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Lexoffice;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use Rappasoft\LaravelLivewireTables\Views\Filters\CheckboxFilter;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class InvoiceDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function bootInvoiceDatatable(): void
    {
        $this->authorize('viewAny', Invoice::class);
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->setTitle("Invoices");

        $this->setDefaultSort('lexoffice_payload->voucher->dueDate', 'desc');

    }

    public function builder(): Builder
    {
        return Invoice::query()->select('invoices.*');
    }

    public function columns(): array
    {
        $icon = "/images/creditreform_icon.png";
        return [
            Column::make("")->from(fn($row) => $row->creditreform_id ? "<span><img src='$icon'></span>" : '')->html(),

            Column::make("Invoice Number")
                ->from(fn($row) => $row->voucher?->voucherNumber)
                ->searchable(
                    fn(Builder $builder, $search) => $builder
                        ->whereJsonContains('lexoffice_payload->voucher->voucherNumber', trim($search))
                        ->orWhere('lexoffice_id', trim($search))
                        ->orWhere('creditreform_id', trim($search))
                ),
            Column::make("Customer")->from(fn($row) => $row->customer),

            Column::make("Total Gross Amount")
                ->from(fn($row) => money($row->total_gross_amount))
                ->sortable(fn(Builder $builder, $direction) => $builder->orderBy('lexoffice_payload->voucher->totalGrossAmount', $direction)),

            Column::make("Total Open Amount")
                ->from(fn($row) => $row->total_open_amount)
                ->sortable(fn(Builder $builder, $direction) => $builder->orderBy('creditreform_payload->totalOpenGrossAmount', $direction)),

            Column::make("Due")
                ->from(fn($row) => Carbon::parse($row->voucher->dueDate)->diffForHumans())
                ->sortable(fn(Builder $builder, $direction) => $builder->orderBy('lexoffice_payload->voucher->dueDate', $direction)),

            Column::make("Notes")->from(fn($row) => $row->creditreform?->notice)->html(),
            Column::make("Dunning Stop")->from(fn($row) => $row->creditreform?->dunningStopUntilDate),
            Column::make("Status")->from(fn($row) => $row->status),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->divider()
                        ->title(fn($row) => 'Details')
                        ->icon('fal fa-eye me-1')
                        ->permission(fn($row) => user()->can('view', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'modals.invoice-details',
                            'params' => ['invoice' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Create Dunning Stop')
                        ->icon('fal fa-clock me-1')
                        ->permission(fn($row) => user()->can('createDunningStop', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.dunning-stop-form',
                            'params' => ['invoice' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Delete Dunning Stop')
                        ->icon('fal fa-ban me-1')
                        ->permission(fn($row) => user()->can('deleteDunningStop', $row))
                        ->attributes(fn($row) => [
                            'wire:click' => "deleteDunningStop($row->id)"
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Write Off')
                        ->icon('fal fa-file-signature me-1')
                        ->permission(fn($row) => user()->can('writeOff', $row))
                        ->attributes(fn($row) => [
                            'wire:click' => "writeOff($row->id)"
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Cancel')
                        ->icon('fal fa-trash me-1')
                        ->permission(fn($row) => user()->can('cancel', $row))
                        ->attributes(fn($row) => [
                            'wire:click' => "cancel($row->id)"
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Export to Creditreform')
                        ->icon('fal fa-file-export me-1')
                        ->permission(fn($row) => user()->can('export', $row))
                        ->attributes(fn($row) => [
                            'wire:click' => "export($row->id)"
                        ]),

                    MenuItem::make('')
                        ->divider('before')
                        ->title(fn($row) => 'Refresh Creditreform Payload')
                        ->icon('fal fa-sync-alt me-1')
                        ->permission(fn($row) => user()->can('updateCreditreformPayload', $row))
                        ->attributes(fn($row) => [
                            'wire:click' => "updateCreditreformPayload($row->id)"
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Refresh Lexoffice Payload')
                        ->icon('fal fa-redo me-1')
                        ->permission(fn($row) => user()->can('updateLexofficePayload', $row))
                        ->attributes(fn($row) => [
                            'wire:click' => "updateLexofficePayload($row->id)"
                        ]),
                ]),
        ];
    }

    public function filters(): array
    {
        return [
            CheckboxFilter::make("Only Open")
                ->label("Show only open invoices")
                ->setFilterDefaultValue(1)
                ->setFilterLabelAttributes([
                    'style' => "visibility: hidden",
                ])
                ->filter(function(Builder $builder, string $value) {
                    $builder->whereJsonContains('lexoffice_payload->voucher->voucherStatus', "open");
                }),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function writeOff(Invoice $invoice): void
    {
        try {
            Creditreform::cancelInvoice($invoice);
        } catch (\Exception $exception){
            $this->dispatch('flashNotification', message: $exception->getMessage(), type: 'error');
            return;
        }

        $this->dispatch('flashNotification', message: __('Operation successful'));
    }

    public function export(Invoice $invoice): void
    {
        SendInvoiceToCreditReformJob::dispatch($invoice);
        $this->dispatch('flashNotification', message: __('Invoice sent to creditreform'));
    }

    public function updateCreditreformPayload(Invoice $invoice): void
    {
        try {
            Creditreform::updateInvoicePayload($invoice);
        } catch (\Exception $exception){
            $this->dispatch('flashNotification', message: $exception->getMessage(), type: 'error');
            return;
        }

        $this->dispatch('flashNotification', message: __('Creditreform data updated successfully'));
    }

    public function updateLexofficePayload(Invoice $invoice): void
    {
        try {
            Lexoffice::updateInvoicePayload($invoice);
        } catch (\Exception $exception){
            $this->dispatch('flashNotification', $exception->getMessage(), type: 'error');
            return;
        }

        $this->dispatch('flashNotification', message: __('Lexoffice data updated successfully'));
    }

    public function cancel(Invoice $invoice): void
    {
        try {
            Creditreform::cancelInvoice($invoice);
        } catch (\Exception $exception){
            $this->dispatch('flashNotification', $exception->getMessage(), type: 'error');
            return;
        }

        $this->dispatch('flashNotification', message: __('Operation successful'));
    }

    public function deleteDunningStop(Invoice $invoice): void
    {
        try {
            Creditreform::deleteDunningStop($invoice);
        } catch (\Exception $exception){
            $this->dispatch('flashNotification', $exception->getMessage(), type: 'error');
            return;
        }

        $this->dispatch('flashNotification', message: __('Operation successful'));
    }

    public function createDunningStop(Invoice $invoice): void
    {
        Creditreform::createDunningStop($invoice);
    }
}
