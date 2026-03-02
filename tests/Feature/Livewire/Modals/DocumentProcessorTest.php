<?php

use App\Livewire\Modals\DocumentProcessor;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Ticket;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    seedDocumentTypes(true);
    loginWithPermissions(Permissions::allPermissions());

    $document = Document::factory()->lexoffice()->create();
    $this->component = Livewire::test(DocumentProcessor::class, ['document' => $document->id]);
});

it('can render modal', function () {
    $this->component->assertSuccessful();
});

it('will show document properties when source and document type is selected', function () {
    expect($this->component->documentProperties)->toHaveCount(0);
    //todo
});

it('will validate form', function () {
    $this->component
        ->call('submit')
        ->assertHasErrors();
});

it('will submit form', function () {
    $documentType = DocumentType::where('key', 'INFO')->first();

    $this->component
        ->set('documentType', $documentType->key)
        ->call('submit')
        ->assertHasErrors();
})->todo();

describe('create ticket', function () {
    it('will show a warning if ticket number is invalid', function () {
        $ticketNumber = 'invalid_ticket_number';

        $this->component
            ->call('createTicket', ticketNumber: $ticketNumber)
            ->assertDispatched('flashNotification', message: "The ticket number $ticketNumber is not valid.")
            ->assertDispatched('modal.close');
    });

    it('will create a ticket with a valid ticket number', function (){
        $ticketNumber = 'TKT-123456-123456';

        $this->component
            ->call('createTicket', ticketNumber: $ticketNumber);

        assertDatabaseHas(Ticket::class, [
            'number' => $ticketNumber
        ]);
    });

    it('will open ticket created alert modal if the ticket is newly created', function (){
        $ticketNumber = 'TKT-123456-123456';

        $this->component
            ->call('createTicket', ticketNumber: $ticketNumber)
            ->assertDispatched('modal.open', component: 'ticket-created-alert');
    });

    it('will not open ticket created alert modal if the ticket is not newly created', function (){
        $ticket = Ticket::factory()->synced()->create();

        $this->component
            ->call('createTicket', ticketNumber: $ticket->number)
            ->assertNotDispatched('modal.open', component: 'ticket-created-alert')
            ->assertDispatched('modal.close');
    });
});
