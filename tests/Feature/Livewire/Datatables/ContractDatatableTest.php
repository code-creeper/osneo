<?php

use App\Livewire\Datatables\ContractDatatable;
use App\Models\Contract;
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
    $this->component = Livewire::test(ContractDatatable::class);

});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show contracts', function () {
    Contract::truncate();
    Contract::factory(3)->create();

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(3);

            return true;
        });
});

it('can delete contract', function () {
    $contract = Contract::factory()->create();

    $this->component
        ->call('delete', $contract->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Contract deleted');

    assertDatabaseMissing(Contract::class, [
        'id' => $contract->id
    ]);
});
