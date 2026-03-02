<?php

use App\Livewire\Datatables\TicketDatatable;
use App\Models\Ticket;
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
    $this->component = Livewire::test(TicketDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show tickets', function () {
    Ticket::forceTruncate();

    Ticket::factory(3)->create();

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(3);

            return true;
        });
});


it('can delete ticket', function (){
    $ticket = Ticket::factory()->create();

    $this->component
        ->call('delete', $ticket->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Ticket deleted successfully');

    assertDatabaseMissing(Ticket::class, [
        'id' => $ticket->id
    ]);
});
