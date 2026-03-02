<?php

use App\Livewire\Forms\PermissionForm;
use App\Models\Role;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $role = Role::first();
    $this->component = Livewire::test(PermissionForm::class, ['role' => $role->id]);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

it('can submit form', function () {
    $this->component
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Permissions updated');
});

