<?php

use App\Livewire\Forms\AddressForm;
use App\Models\Address;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(AddressForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

describe('validation', function (){
    it('will validate form', function (){
        $this->component
            ->call('submit')
            ->assertHasErrors();
    })->todo();

    test('validation rules', function ($address, $errors){
        $setValue = fn($key, $value) => array_key_exists($key, $address) ? $address[$key] : $value;
        $address = [
            'street' => $setValue('street', '453 street'),
            'zip_code' => $setValue('zip_code', '12345'),
            'city' => $setValue('city', 'berlin'),
            'is_service_location' => $setValue('is_service_location', null),
            'heating_system' => $setValue('heating_system', null),
        ];

        $this->component
            ->set('address.street', $address['street'])
            ->set('address.zip_code', $address['zip_code'])
            ->set('address.city', $address['city'])
            ->set('address.is_service_location', $address['is_service_location'])
            ->set('address.heating_system', $address['heating_system'])
            ->call('submit')
            ->assertHasErrors($errors);
    })->with([
        'street is required' => [
            ['street' => null],
            'address.street',
        ],
        'zip_code is required' => [
            ['zip_code' => null],
            'address.zip_code',
        ],
        'city is required' => [
            ['city' => null],
            'address.city',
        ],
        'heating_system is required with service location' => [
            ['is_service_location' => true, 'heating_system' => null],
            'address.heating_system',
        ],
    ]);
});

it('can submit form', function () {
    $address = Address::factory()->make();

    $this->component
        ->call('submit')
        ->assertHasErrors()

        ->set('address.street', $address->street)
        ->set('address.zip_code', $address->zip_code)
        ->set('address.city', $address->city)
        ->call('submit')
        ->assertDispatched('addressCreated')
        ->assertDispatched('flashNotification', message: 'Address created');
});
