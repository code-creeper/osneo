<?php

use App\Livewire\Datatables\LeaveReasonDatatable;
use App\Models\LeaveReason;
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
    $this->component = Livewire::test(LeaveReasonDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show leave reasons', function () {
    LeaveReason::forceTruncate();

    LeaveReason::factory(3)->create();

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(3);

            return true;
        });
});


it('can delete leave reason', function (){
    $reason = LeaveReason::factory()->create();

    $this->component
        ->call('delete', $reason->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Leave Reason deleted');

    assertDatabaseMissing(LeaveReason::class, [
        'id' => $reason->id
    ]);
});
