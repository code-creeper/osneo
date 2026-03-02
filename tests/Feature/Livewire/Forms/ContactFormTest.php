<?php

use App\Livewire\Forms\ContactForm;
use App\Models\Address;
use App\Models\Contact;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(ContactForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

describe('validation', function (){
    it('will validate form', function () {
        $this->component
            ->call('submit')
            ->assertHasErrors();
    });

    test('validation rules', function ($contact, $errors){
        $address = Address::factory()->create();

        $setValue = fn($key, $value) => array_key_exists($key, $contact) ? $contact[$key] : $value;
        $contact = [
            'name' => $setValue('name', 'test customer'),
            'first_name' => $setValue('first_name', 'test'),
            'last_name' => $setValue('last_name', 'customer'),
            'is_company' => $setValue('is_company', 0),
            'is_customer' => $setValue('is_customer', 0),
            'is_supplier' => $setValue('is_supplier', 0),
            'invoice_method' => $setValue('invoice_method', null),
            'manager_id' => $setValue('manager_id', null),
            'email' => $setValue('email', 'test@customer.com'),
            'phone' => $setValue('phone', null),
            'billing_address_id' => $setValue('billing_address_id', $address->id),
        ];

        $this->component
            ->set('contact.name', $contact['name'])
            ->set('contact.first_name', $contact['first_name'])
            ->set('contact.last_name', $contact['last_name'])
            ->set('contact.is_company', $contact['is_company'])
            ->set('contact.is_customer', $contact['is_customer'])
            ->set('contact.is_supplier', $contact['is_supplier'])
            ->set('contact.customer', [
                'invoice_method' => $contact['invoice_method']
            ])
            ->set('contact.manager_id', $contact['manager_id'])
            ->set('contact.email', $contact['email'])
            ->set('contact.phone', $contact['phone'])
            ->set('contact.billing_address_id', $contact['billing_address_id'])
            ->call('submit')
            ->assertHasErrors($errors);
    })->with([
        'name is required without first_name' => [
            ['name' => null, 'first_name' => null],
            'contact.name',
        ],
        'name is required without last_name' => [
            ['name' => null, 'last_name' => null],
            'contact.name',
        ],
        'name is required without first_name and last_name' => [
            ['name' => null, 'first_name' => null, 'last_name' => null],
            'contact.name',
        ],
        'first_name is required without name' => [
            ['name' => null, 'first_name' => null],
            'contact.first_name',
        ],
        'last_name is required without name' => [
            ['name' => null, 'last_name' => null],
            'contact.last_name',
        ],
        'invoice_method is required if contact is customer' => [
            ['invoice_method' => null, 'is_customer' => '1'],
            'contact.customer.invoice_method',
        ],
        'email is required if customer invoice method is email' => [
            ['invoice_method' => 'Email', 'email' => null],
            'contact.email',
        ],
        'email should be valid' => [
            ['email' => 'invalid_email'],
            'contact.email',
        ],
    ]);
});

it('can submit form', function ($contact, $additionalAssertions = []) {
    $address = Address::factory()->create();
    $manager = Contact::factory()->create();

    $setValue = fn($key, $value) => array_key_exists($key, $contact) ? $contact[$key] : $value;
    $contact = [
        'name' => $setValue('name', 'test customer'),
        'first_name' => $setValue('first_name', 'test'),
        'last_name' => $setValue('last_name', 'customer'),
        'is_company' => $setValue('is_company', 0),
        'is_customer' => $setValue('is_customer', 0),
        'is_supplier' => $setValue('is_supplier', 0),
        'invoice_method' => $setValue('invoice_method', null),
        'manager_id' => $setValue('manager_id', $manager->id),
        'email' => $setValue('email', 'test@customer.com'),
        'phone' => $setValue('phone', '111-111-111'),
        'billing_address_id' => $setValue('billing_address_id', $address->id),
    ];

    $this->component
        ->set('contact.name', $contact['name'])
        ->set('contact.first_name', $contact['first_name'])
        ->set('contact.last_name', $contact['last_name'])
        ->set('contact.is_company', $contact['is_company'])
        ->set('contact.is_customer', $contact['is_customer'])
        ->set('contact.is_supplier', $contact['is_supplier'])
        ->set('contact.customer', [
            'invoice_method' => $contact['invoice_method']
        ])
        ->set('contact.manager_id', $contact['manager_id'])
        ->set('contact.email', $contact['email'])
        ->set('contact.phone', $contact['phone'])
        ->set('contact.billing_address_id', $contact['billing_address_id'])
        ->call('submit')
        ->assertDispatched('contactCreated')
        ->assertDispatched('flashNotification', message: 'Contact created');

    $databaseRecord = array_merge($contact, $additionalAssertions, [
        'customer' => $this->castAsJson(['invoice_method' => $contact['invoice_method']])
    ]);
    unset($databaseRecord['invoice_method']);

    assertDatabaseHas(Contact::class, $databaseRecord);
})->with([
    'submit complete form' => [
        [],
    ],
    'submit with only required fields' => [
        [
            'manager_id' => null,
            'email' => null,
            'phone' => null,
        ],
    ],
    'submit with company name' => [
        [
            'name' => 'hello',
            'first_name' => null,
            'last_name' => null
        ],
    ],
    'submit with first and last name' => [
        [
            'first_name' => 'first',
            'last_name' => 'last',
            'name' => null,
        ],
        ['name' => 'first last'],
    ],
    'submit with customer' => [
        [
            'is_customer' => 1,
            'invoice_method' => 'Email',
        ],
    ],
]);

it('can handle address creation', function () {
    $address = Address::factory()->create();

    $this->component
        ->assertSet('contact.billing_address_id', null)
        ->call('newAddressCreated', addressId: $address->id)
        ->assertSet('contact.billing_address_id', $address->id)
        ->assertSet('address', $address->fullAddress());
});
