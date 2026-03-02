<?php

use App\Livewire\Forms\PreferenceForm;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $user = auth()->user();
    $this->component = Livewire::test(PreferenceForm::class, ['model' => $user->id, 'type' => 'User']);
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
    $this->component
        ->set('preferences.leave_increment_start_year', 2012)
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Preferences saved');
});
