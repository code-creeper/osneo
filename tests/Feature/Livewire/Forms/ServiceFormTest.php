<?php

use App\Livewire\Forms\ServiceForm;
use App\Models\Service;
use App\Models\ServiceCategory;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(ServiceForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

it('will validate form', function () {
    $this->component
        ->call('submit')
        ->assertHasErrors();
});

it('can submit service form', function () {
    $service = Service::factory()->make();

    $this->component
        ->set('service.name', $service->name)
        ->set('service.unit', $service->unit)
        ->set('sizes', $service->sizes->toArray())
        ->set('service.description', $service->description)
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Service updated');

    assertDatabaseHas(Service::class, [
        'name' => $service->name
    ]);
});

it('can manage sizes', function () {
    $this->component
        ->assertSet('sizes', [])
        ->call('addSize')
        ->assertSet('sizes', [
            [
                'name' => null,
                'price' => 0.00,
            ]
        ])
        ->call('removeSize', 0)
        ->assertSet('sizes', []);
});

it('can copy category description', function () {
    $category = ServiceCategory::factory()->create();

    $this->component
        ->set('service.service_category_id', $category->id)
        ->call('copyCategoryDescription')
        ->assertSet('service.description', $category->description);
});

