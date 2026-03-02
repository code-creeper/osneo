<?php

use App\Livewire\Datatables\PayrollDatatable;
use App\Livewire\Datatables\TicketDatatable;
use App\Models\Ticket;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());
    $this->component = Livewire::test(PayrollDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show payrolls', function () {

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(3);

            return true;
        });
})->todo();
