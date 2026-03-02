<?php

use App\Livewire\Forms\ServiceCategoryForm;
use App\Models\ServiceCategory;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(ServiceCategoryForm::class);
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
    $category = ServiceCategory::factory()->make();

    $this->component
        ->set('serviceCategory.name', $category->name)
        ->set('serviceCategory.description', $category->description)
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Service category updated');

    assertDatabaseHas(ServiceCategory::class, [
        'name' => $category->name
    ]);
});


