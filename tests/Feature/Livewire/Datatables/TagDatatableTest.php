<?php

use App\Livewire\Datatables\TagDatatable;
use App\Models\Tag;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());
    $this->component = Livewire::test(TagDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show tags', function () {
    Tag::factory(3)->create();

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(3);

            return true;
        });
});


it('can delete tag', function (){
    $tag = Tag::factory()->create();

    $this->component
        ->call('delete', $tag->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Tag deleted');

    assertDatabaseEmpty(Tag::class);
});
