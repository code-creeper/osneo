<?php

use App\Livewire\Modals\DocumentViewer;
use App\Models\Document;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

it('can render modal', function () {
    $document = Document::factory()->withFile()->create();
    Livewire::test(DocumentViewer::class, ['document' => $document])
        ->call('$refresh')
        ->assertSuccessful()
        ->assertNotDispatched('flashNotification');
});

it('will show a warning if document url is not found', function () {
    $document = Document::factory()->create();

    Livewire::test(DocumentViewer::class, ['document' => $document])
        ->call('$refresh')
        ->assertDispatched('flashNotification', message: 'Failed to open the document', type: 'error');
});
