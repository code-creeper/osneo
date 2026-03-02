<?php

namespace App\Livewire\Forms;

use App\Enums\ContactType;
use App\Livewire\Traits\LogsActivity;
use App\Models\Address;
use App\Models\Contact;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use WireElements\Pro\Components\Modal\Modal;

class ContactForm extends Modal
{
    use LogsActivity;

    public Contact|int $contact;

    public string $title;
    public bool $editing = false;

    public array $companies = [];
    public ?string $address = null;

    protected $listeners = ['addressCreated' => 'newAddressCreated'];

    protected function rules(): array
    {
        return [
            'contact.name' => 'required_without:contact.first_name,contact.last_name',
            'contact.first_name' => 'required_without:contact.name',
            'contact.last_name' => 'required_without:contact.name',
            'contact.is_company' => 'nullable',
            'contact.is_customer' => 'nullable',
            'contact.is_supplier' => 'nullable',
            'contact.customer.invoice_method' => 'required_if:contact.is_customer,true',
            'contact.manager_id' => 'nullable',
            'contact.email' => 'nullable|email|required_if:contact.customer.invoice_method,Email',
            'contact.phone' => 'nullable',
            'contact.billing_address_id' => 'required',
        ];
    }

    public function mount(Contact $contact): void
    {
        $this->title = __('Create Contact');
        $this->contact = $contact;

        if ($this->contact->id){
            $this->editing = true;
            $this->title = __('Edit Contact');
            $this->address = $this->contact->billingAddress?->fullAddress();

        } else {
            $contact->is_company = 0;
            $contact->is_customer = 0;
            $contact->is_supplier = 0;

            $this->contact->customer = [];
        }
    }

    public function render(): View
    {
        $data = array();

        $data['contact_types'] = ContactType::cases();
        $data['companies'] = $this->companies = Contact::all()->toKeyValuePair();

        return view('livewire.forms.contact-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->contact->save();

        $this->close(andDispatch: [
            'contactCreated' => ['contactId' => $this->contact->id],
            'flashNotification' => ['message' => __('Contact created')],
        ]);
    }

    public function newAddressCreated(int $addressId): void
    {
        $address = Address::find($addressId);
        $this->address = $address->fullAddress();
        $this->contact->billing_address_id = $address->id;
    }

    public static function attributes(): array
    {
        return [
            'size' => '7xl',
        ];
    }
}
