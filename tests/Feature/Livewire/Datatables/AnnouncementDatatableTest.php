<?php

use App\Livewire\Datatables\AnnouncementDatatable;
use App\Models\Announcement;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());
    $this->component = Livewire::test(AnnouncementDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show announcements', function () {
    Announcement::truncate();
    Announcement::factory(3)->create();

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(3);

            return true;
        });
});

it('can delete announcement', function (){
    $announcement = Announcement::factory()->create();

    $this->component
        ->call('delete', $announcement->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Announcement deleted');

    assertDatabaseMissing(Announcement::class, ['id' => $announcement->id]);
});
