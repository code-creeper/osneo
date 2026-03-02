<?php

use App\Livewire\Modals\AnnouncementModal;
use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $user = User::factory()
        ->hasAttached(Announcement::factory(), [
            'read_at' => now(),
        ])
        ->hasUnreadAnnouncements(3)
        ->create();

    login($user);

    $this->component = Livewire::test(AnnouncementModal::class);
});

it('can render modal', function () {
    $this->component->assertSuccessful();
});

it('will preview modal', function () {
    $announcement = Announcement::factory()->create();
    $component = Livewire::test(AnnouncementModal::class, ['announcement' => $announcement]);

    $component
        ->assertDontSeeText('strong class="text-info"')
        ->assertDontSeeText('wire:click="markAsRead"')
        ->assertSeeHtml("delete-announcement")
        ->assertSeeHtml('wire:modal="forms.announcement-form');
});

it('can initialize', function () {
    $this->component->call('initialize');

    expect($this->component->announcements)->toHaveCount(3);
    $this->component->assertSeeText('Announcement 1/3');

});

it('can show next announcement', function () {
    $this->component
        ->call('next')->assertSeeText('Announcement 2/3')
        ->call('next')->assertSeeText('Announcement 3/3')
        ->assertDontSeeHtml('wire:click="next"')
        ->call('next')->assertSeeText('Announcement 3/3');
});

it('can show previous announcement', function () {
    $this->component
        ->set('index', 2)
        ->call('previous')->assertSeeText('Announcement 2/3')
        ->call('previous')->assertSeeText('Announcement 1/3')
        ->assertDontSeeHtml('wire:click="previous"')
        ->call('previous')->assertSeeText('Announcement 1/3');
});

it('can mark all announcements as read', function ($index, $current) {
    $this->component
        ->set('index', $index)
        ->call('markAsRead')
        ->assertSeeText("Announcement $current/2");

    expect($this->component->announcements)->toHaveCount(2);
})->with([
    [0, 1],
    [1, 2],
    [2, 2],
]);

it('can mark announcements as read', function () {
    $this->component
        ->assertDontSeeHtml('wire:click="markAllAsRead"')
        ->set('index', 2)
        ->assertSeeHtml('wire:click="markAllAsRead"')
        ->call('markAllAsRead')
        ->assertDispatched('modal.close');

    expect(user()->unreadAnnouncements)->toHaveCount(0);
});
