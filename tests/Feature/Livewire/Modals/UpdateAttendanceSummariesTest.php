<?php

use App\Jobs\UpdateAttendanceSummariesJob;
use App\Livewire\Modals\UpdateAttendanceSummaries;
use App\Models\Attendance;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(UpdateAttendanceSummaries::class);
});

it('can render modal', function () {
    $this->component->assertSuccessful();
});

it('can submit form', function () {
    Attendance::factory()->create();
    $this->component
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Summaries are being updated now');

    Bus::assertDispatched(UpdateAttendanceSummariesJob::class);
});
