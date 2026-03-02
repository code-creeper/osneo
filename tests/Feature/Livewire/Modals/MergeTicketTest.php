<?php

use App\Livewire\Modals\MergeTicket;
use App\Models\Ticket;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(MergeTicket::class);
});

it('can render modal', function () {
    $this->component->assertSuccessful();
});

it('can merge ticket', function () {
    $baseTicket = Ticket::factory()->hasDocuments(3)->create();
    $newTicket = Ticket::factory()->create();

    expect($baseTicket->documents)->toHaveCount(3)
        ->and($newTicket->documents)->toHaveCount(0);

    Livewire::test(MergeTicket::class, ['ticket' => $baseTicket->id])
        ->set('ticketId', $newTicket->id)
        ->call('submit')
        ->assertDispatched('flashNotification');

    $newTicket->refresh();
    assertDatabaseMissing(Ticket::class, [
        'id' => $baseTicket->id,
    ]);
    expect($newTicket->documents)->toHaveCount(3);
});
