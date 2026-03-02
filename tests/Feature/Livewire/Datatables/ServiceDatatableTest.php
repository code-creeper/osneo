<?php

use App\Livewire\Datatables\ServiceDatatable;
use App\Models\Service;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());
    $this->component = Livewire::test(ServiceDatatable::class);

});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show services', function () {
    Service::truncate();
    Service::factory(10)->create();

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(10);

            return true;
        });
});

it('can delete service', function () {
    $service = Service::factory()->create();

    $this->component
        ->call('delete', $service->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Service deleted');

    assertDatabaseMissing(Service::class, [
        'id' => $service->id
    ]);
});
