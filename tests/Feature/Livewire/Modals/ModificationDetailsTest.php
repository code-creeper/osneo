<?php

use App\Livewire\Modals\ModificationDetails;
use App\Models\Modification;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $modification = Modification::factory()->create();
    $this->component = Livewire::test(ModificationDetails::class, ['modification' => $modification->id]);
});

it('can render modal', function () {
    $this->component->assertSuccessful();
});
