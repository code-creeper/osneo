<?php

use App\Livewire\Forms\DunningStopForm;
use App\Models\Invoice;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $invoice = Invoice::factory()->create();
    $this->component = Livewire::test(DunningStopForm::class, ['invoice' => $invoice]);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

it('can submit form', function () {
    $this->component
        ->set('date', now()->date())
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Dunning stop created');
});

it('will validate form', function () {
    $this->component
        ->call('submit')
        ->assertHasErrors();
});
