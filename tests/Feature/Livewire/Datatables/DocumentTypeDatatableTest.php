<?php

use App\Livewire\Datatables\DocumentTypeDatatable;
use App\Models\DocumentType;
use Database\Seeders\DocumentTypeSeeder;
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
    $this->component = Livewire::test(DocumentTypeDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show document types', function () {
    DocumentType::factory()->create();

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows->count())->toBeGreaterThan(0);

            return true;
        });
});


it('can delete documentType', function (){
    $documentType = DocumentType::factory()->create();

    $this->component
        ->call('delete', $documentType->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Document Type deleted');

    assertDatabaseMissing(DocumentType::class, [
        'id' => $documentType->id
    ]);
});
