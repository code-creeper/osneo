<?php

use App\Livewire\Forms\ContractForm;
use App\Models\Constant;
use App\Models\Contact;
use App\Models\Contract;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(ContractForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

describe('validate form', function (){

})->todo();

it('can submit contract form', function () {
    $contact = Contact::factory()->create();
    $contract = Contract::factory()->for($contact, 'customer')->make();

    $this->component
        ->set('contract.contact_id', $contact->id)
        ->set('contractServices', $contract->services->toArray())
        ->set('sections', $contract->sections)
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Contract updated');

    assertDatabaseHas(Contract::class, [
        'name' => $contract->name
    ]);
})->skip('need to fix issues');

it('can manage services', function () {
    $service = [
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

    $this->component
        ->assertSet('services', [])
        ->call('addService')
        ->assertSet('contractServices', [
            $service
        ])
        ->call('removeService', 0)
        ->assertSet('contractServices', []);
});

describe('contract sections', function (){
    it('can set contract sections from constants', function () {
        Constant::create([
            'group' => 'contract_sections',
            'key' => 'title',
            'value' => 'some title'
        ]);

        $this->component
            ->call('setContractSections')
            ->assertSet('sections.title', 'some title');
    });

    it('can set default contract sections', function () {
        $defaults = config('lexoffice.defaults');

        $this->component
            ->call('setContractSections')
            ->assertSet('sections.title', $defaults['title'])
            ->assertSet('sections.introduction', $defaults['introduction'])
            ->assertSet('sections.remarks', $defaults['remarks'])
            ->assertSet('sections.payment_terms.duration', $defaults['payment_terms']['duration'])
            ->assertSet('sections.payment_terms.label', $defaults['payment_terms']['label']);
    });
});


