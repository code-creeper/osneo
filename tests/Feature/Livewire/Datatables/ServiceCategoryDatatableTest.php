<?php

use App\Livewire\Datatables\ServiceCategoryDatatable;
use App\Models\ServiceCategory;
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
    $this->component = Livewire::test(ServiceCategoryDatatable::class);

});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show service categories', function () {
    ServiceCategory::forceTruncate();
    ServiceCategory::factory(10)->create();

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(10);

            return true;
        });
});

it('can delete service category', function () {
    $category = ServiceCategory::factory()->create();

    $this->component
        ->call('delete', $category->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Service Category deleted');

    assertDatabaseMissing(ServiceCategory::class, [
        'id' => $category->id
    ]);
});
