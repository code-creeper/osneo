<?php

use App\Livewire\Modals\ProcessInsuranceClaim;
use App\Models\Leave;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $leave = Leave::factory()->create();
    $this->component = Livewire::test(ProcessInsuranceClaim::class, ['leave' => $leave]);
});

it('can render modal', function () {
    $this->component->assertSuccessful();
});
