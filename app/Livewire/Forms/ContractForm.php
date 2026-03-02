<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\Constant;
use App\Models\Contact;
use App\Models\Contract;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Ticket;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use WireElements\Pro\Components\Modal\Modal;

class ContractForm extends Modal
{
    use LogsActivity;

    public Contract|int $contract;

    public string $heading;
    public bool $editing = false;
    public string|null $ticketNumber = null;

    public bool $formSubmitted = false;

    public array $contacts = [];
    public array $contractServices = [];

    public array $services = [];
    public array $categories = [];
    public array $sizes = [];
    public array $sections = [];

    public Collection $allServices;
    public Collection $allCategories;

    protected $listeners = ['contactCreated' => 'newContactCreated'];

    protected function rules(): array
    {
        return [
            'contract.contact_id' => 'required',
            'ticketNumber' => 'nullable',
            'contractServices.*.category_id' => 'nullable',
            'contractServices.*.service_id' => 'required',
            'contractServices.*.size' => 'required',
            'sections.title' => 'required',
            'sections.introduction' => 'required|max:2000',
            'sections.payment_terms.label' => 'required',
            'sections.payment_terms.duration' => 'required',
            'sections.remarks' => 'required|max:2000',
        ];
    }

    public function mount(Contract $contract): void
    {
        $this->allServices = Service::all();
        $this->allCategories = ServiceCategory::all();

        $this->heading = __('Create Contract');
        $this->contract = $contract;

        $this->contacts = Contact::all()->toKeyValuePair();
        $this->categories = $this->allCategories->toKeyValuePair();

        $this->setContractSections();

        if ($this->contract->id){
            $this->contract = $contract->replicate();
            $this->editing = true;
            $this->heading = __('Edit Contract');
            $this->ticketNumber = $contract->ticket?->number;

            $this->contractServices = $this->contract->services->toArray();
            $this->sections = $this->contract->sections->toArray();

            foreach ($this->contract->services as $index => $service){
                $this->prepareServices($index, $service['category_id']);
                $this->prepareSizes($index, $service['service_id']);
            }
        } else {
            $this->contract->contact_id = null;
        }
    }

    public function setContractSections(): void
    {
        $this->sections = config('lexoffice.defaults');

        $contractSections = Constant::group('contract_sections')->get();
        $title = $contractSections?->where('key', 'title')->value('value');
        $introduction = $contractSections?->where('key', 'introduction')->value('value');
        $remarks = $contractSections?->where('key', 'remarks')->value('value');
        $payment_duration = $contractSections?->where('key', 'payment_terms_duration')->value('value');
        $payment_terms = $contractSections?->where('key', 'payment_terms_label')->value('value');

        $this->sections['title'] = $title ?: $this->sections['title'];
        $this->sections['introduction'] = $introduction ?: $this->sections['introduction'];
        $this->sections['remarks'] = $remarks ?: $this->sections['remarks'];
        $this->sections['payment_terms']['duration'] = $payment_duration ?: $this->sections['payment_terms']['duration'];
        $this->sections['payment_terms']['label'] = $payment_terms ?: $this->sections['payment_terms']['label'];
    }

    public function render(): View
    {
        $data = array();

        return view('livewire.forms.contract-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $ticket = $this->ticketNumber
            ? Ticket::firstOrCreate(['number' => $this->ticketNumber])
            : null;

        $this->contract->ticket_id = $ticket?->id;
        $this->contract->services = $this->contractServices;
        $this->contract->sections = $this->sections;
        $this->contract->save();

        $this->formSubmitted = true;

        $document = $this->contract->getDocument();

        $this->dispatch('refresh');

        //todo:: not working for now due to the livewire limitations. needs some workaround!
        if ($ticket?->wasRecentlyCreated){
             $this->dispatch('flashNotification', message: __('New ticket created'));
            //$this->dispatch('modal.open', component: 'ticket-created-alert', arguments: ['ticket' => $ticket->id]);
        }

        if($document){
            $this->dispatch('modal.open', component: 'modals.document-viewer', arguments: ['document' => $document->id, 'forceClose' => true]);
        } else {
            $this->close(andDispatch: [
                'flashNotification' => ['message' => __('Contract updated')],
            ]);
        }
    }

    public function onUpdateCategory(int $index, int $categoryId): void
    {
        $this->prepareServices($index, $categoryId);
    }

    public function updatedContractServices($value, $key): void
    {
        $index = str($key)->before('.')->value();
        $key = str($key)->after('.');


        if (in_array($key, ['category_id', 'service_id'])){
            $service = $this->allServices->find($this->contractServices[$index]['service_id']);

            // if category or service gets changed, reset size unit and price
            $this->contractServices[$index]['size_id'] = '';
            $this->contractServices[$index]['size'] = null;
            $this->contractServices[$index]['price'] = null;
            $this->contractServices[$index]['unit'] = null;
            $this->sizes[$index] = [];
        }

        if($key == 'category_id'){
            $serviceCategory = $this->allCategories->find($this->contractServices[$index]['category_id']);
            $this->contractServices[$index]['category_name'] = $serviceCategory?->name;
            $this->contractServices[$index]['description'] = $serviceCategory?->description;

            //reset service
            $this->contractServices[$index]['service_id'] = '';

            $this->prepareServices($index, $value);
        }

        if($key == 'service_id'){
            $this->contractServices[$index]['service_name'] = $service?->name;
            $this->contractServices[$index]['description'] ??= $service?->description;

            $this->prepareSizes($index, $value);
        }

        if($key == 'size_id'){
            $sizes = $service?->sizes;
            $this->contractServices[$index]['price'] = $sizes[$value]['price'];
            $this->contractServices[$index]['size'] = $sizes[$value]['name'];
            $this->contractServices[$index]['unit'] = $service?->unit;
        }
    }

    public function prepareServices(int $index, int $categoryId = null): void
    {
        $services = $this->allServices->where('service_category_id', $categoryId);
        $this->services[$index] = $services->toKeyValuePair();
    }

    public function prepareSizes(int $index, int $serviceId = null): void
    {
        $service = $this->allServices->find($serviceId);
        $sizes = $service?->sizes->pluck('name')->toArray() ?? array();

        $this->sizes[$index] = $sizes;
    }

    public function newContactCreated(int $contactId): void
    {
        $this->contract->contact_id = $contactId;
    }

    public function removeService(int $index): void
    {
        unset($this->contractServices[$index]);
    }

    public function addService(): void
    {
        $index = count($this->contractServices);
        $this->prepareServices($index);
        $this->prepareSizes($index);

        $this->contractServices[] = [
            'category_id' => '',
            'service_id' => '',
            'category_name' => null,
            'service_name' => null,
            'description' => null,
            'size_id' => '',
            'size' => '',
            'unit' => null,
            'price' => null,
        ];
    }

    public static function attributes(): array
    {
        return [
            'size' => '7xl',
        ];
    }
}
