<?php

use App\Livewire\Forms\ConstantForm;
use App\Models\Constant;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(ConstantForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

it('can submit form', function () {
    $this->component
        ->set('constant.group', 'general')
        ->set('constant.key', 'some_key')
        ->set('constant.value', 'some_value')
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Constant saved');

    assertDatabaseHas(Constant::class, [
        'group' => 'general',
        'key' => 'some_key'
    ]);
});

it('will validate form', function () {
    $this->component
        ->call('submit')
        ->assertHasErrors();
});
