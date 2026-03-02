<?php

use App\Livewire\Datatables\InvoiceDatatable;
use App\Models\Invoice;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());
    $this->component = Livewire::test(InvoiceDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show invoices', function () {
    Invoice::factory(3)->open()->create();

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(3);

            return true;
        });
});

