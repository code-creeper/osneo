<?php

use App\Livewire\Forms\TicketForm;
use App\Models\Ticket;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(TicketForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

it('can submit form', function () {
    $ticketNumber = 'TKT-123456-123456';

    $this->component
        ->set('ticket.number', $ticketNumber)
        ->call('submit')
        ->assertSuccessful()
        ->assertDispatched('flashNotification', message: 'Ticket updated');

    assertDatabaseHas(Ticket::class, ['number' => $ticketNumber]);
});

describe('validation', function (){
    it('will validate ticket number format', function ($ticketNumber) {
        $this->component
            ->set('ticket.number', $ticketNumber)
            ->call('submit')
            ->assertHasErrors();
    })->with([
        'ABC-123456-123456', // invalid format
        '123-123456-123456', // invalid format
        'TKT-123456-12345', // invalid length
        'TKT-A23456-123456', // invalid format
        'TKT-123456-A23456', // invalid format
    ]);

    it('will validate unique ticket number', function (){
        $ticketNumber = 'TKT-123456-123456';

        Ticket::create(['number' => $ticketNumber]);
        $this->component
            ->set('ticket.number', $ticketNumber)
            ->call('submit')
            ->assertHasErrors();
    });
});
