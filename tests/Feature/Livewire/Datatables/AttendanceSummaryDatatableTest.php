<?php

use App\Livewire\Datatables\AttendanceSummariesDatatable;
use App\Models\AttendanceSummary;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());
    $this->component = Livewire::test(AttendanceSummariesDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show attendance summaries', function () {
    AttendanceSummary::truncate();

    AttendanceSummary::factory(3)->create([
        'date' => now()
    ]);

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(3);

            return true;
        });
});

