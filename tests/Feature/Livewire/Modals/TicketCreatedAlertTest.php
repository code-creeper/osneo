<?php

use App\Livewire\Modals\TicketCreatedAlert;
use App\Models\Ticket;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $ticket = Ticket::factory()->create();
    $this->component = Livewire::test(TicketCreatedAlert::class, ['ticket' => $ticket]);
});

it('can render modal', function () {
    $this->component->assertSuccessful();
});
