<?php

use App\Livewire\Datatables\DocumentPropertyDatatable;
use App\Models\DocumentProperty;
use App\Models\DocumentType;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    seedDocumentTypes(true);
    if ( ! DocumentType::count()) {
        seed(DocumentTypeSeeder::class);
    }

    if ( ! DocumentProperty::count()) {
        seed(DocumentPropertySeeder::class);
    }

    loginWithPermissions(Permissions::allPermissions());


    $documentType = DocumentType::first();
    $this->component = Livewire::test(DocumentPropertyDatatable::class, ['documentType' => $documentType]);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show document properties', function () {
    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows->count())->toBeGreaterThan(0);

            return true;
        });
});


it('can delete document property', function (){
    $documentProperty = DocumentProperty::first();

    $this->component
        ->call('delete', $documentProperty->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Document Property deleted');

    assertDatabaseMissing(DocumentProperty::class, [
        'id' => $documentProperty->id
    ]);
});
