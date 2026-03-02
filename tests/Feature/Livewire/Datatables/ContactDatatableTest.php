<?php

use App\Livewire\Datatables\ContactDatatable;
use App\Models\Contact;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());
    $this->component = Livewire::test(ContactDatatable::class);

});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('can delete contact', function () {
    $contact = Contact::factory()->create();

    $this->component
        ->call('delete', $contact->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Contact deleted');

    assertDatabaseMissing(Contact::class, [
        'id' => $contact->id
    ]);
});
