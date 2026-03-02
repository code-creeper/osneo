<?php

use App\Livewire\Forms\LeaveReasonForm;
use App\Models\LeaveReason;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(LeaveReasonForm::class);
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
        ->set('name.en', 'test')
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Leave reason updated');

    assertDatabaseHas(LeaveReason::class, [
        'name' => 'test'
    ]);
});
