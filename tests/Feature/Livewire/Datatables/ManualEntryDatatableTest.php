<?php

use App\Livewire\Datatables\ManualEntryDatatable;
use App\Models\ManualEntry;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());
    $this->component = Livewire::test(ManualEntryDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show manual entries', function () {
    ManualEntry::factory(3)->create();

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(3);

            return true;
        });
});


it('can delete manual entry', function (){
    $entry = ManualEntry::factory()->create();

    $this->component
        ->call('delete', $entry->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Entry deleted');

    assertDatabaseEmpty(ManualEntry::class);
});
