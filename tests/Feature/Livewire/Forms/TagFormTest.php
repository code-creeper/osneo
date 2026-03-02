<?php

use App\Livewire\Forms\TagForm;
use App\Models\Tag;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(TagForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

it('will validate form', function () {
    $this->component
        ->call('submit')
        ->assertHasErrors();
});

it('can submit form', function () {
    $tag = Tag::factory()->make();

    $this->component
        ->set('tag.name', $tag->name)
        ->set('tag.model', $tag->model)
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Tag saved');

    assertDatabaseHas(Tag::class, [
        'name' => $tag->name,
        'model' => $tag->model
    ]);
});

