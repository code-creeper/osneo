<?php

use App\Livewire\Modals\AssignDocumentUsers;
use App\Models\Document;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(AssignDocumentUsers::class);
});

it('can render modal', function () {
    $this->component->assertSuccessful();
});

it('will validate form', function () {
    $this->component
        ->call('submit')
        ->assertHasErrors();
});

it('can submit form', function () {
    $document = Document::factory()->create();
    $component = Livewire::test(AssignDocumentUsers::class, ['document' => $document]);

    User::factory(2)->create();
    $assignedUsers = User::all()->pluck('id')->toArray();

    $component
        ->set('selectedUsers', $assignedUsers)
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Users assigned to document');

    $documentUsers = $document->users()->get()->pluck('id')->toArray();

    expect($documentUsers)->toBe($assignedUsers);
});
