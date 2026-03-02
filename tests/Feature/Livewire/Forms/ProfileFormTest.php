<?php

use App\Livewire\Forms\ProfileForm;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(ProfileForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

it('will validate form', function () {
    $this->component
        ->set('user.first_name')
        ->call('submit')
        ->assertHasErrors();
});

it('can submit form', function () {
    $this->component
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Profile updated successfully');
});
