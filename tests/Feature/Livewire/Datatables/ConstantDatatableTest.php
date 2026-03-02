<?php

use App\Livewire\Datatables\ConstantDatatable;
use App\Models\Constant;
use Database\Seeders\ConstantSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    seed(ConstantSeeder::class);
    loginWithPermissions(Permissions::allPermissions());
    $this->component = Livewire::test(ConstantDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show constants', function () {
    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows->count())->toBeGreaterThan(0);

            return true;
        });
});

it('can delete constant', function (){
    $constant = Constant::first();

    $this->component
        ->call('delete', $constant->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Constant deleted');

    assertDatabaseMissing(Constant::class, [
        'id' => $constant->id
    ]);
});
