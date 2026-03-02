<?php

use App\Livewire\Modals\AddPayout;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $user = auth()->user();
    $monthWithYear = now()->format('Y-m');
    $this->component = Livewire::test(AddPayout::class, ['user' => $user->id, 'overtime' => 1, 'monthWithYear' => $monthWithYear]);
});

it('can render modal', function () {
    $this->component->assertSuccessful();
});
