<?php

use App\Livewire\Datatables\ActivityDatatable;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());
    $this->component = Livewire::test(ActivityDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

