<?php

use App\Livewire\Forms\AnnouncementForm;
use App\Models\Announcement;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(AnnouncementForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

it('can submit form', function () {
    DB::truncate('announcement_user');

    $this->component
        ->call('submit')
        ->assertHasErrors();

    fillAnnouncementForm($this->component);
    $this->component
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Announcement saved');

    assertDatabaseCount('announcement_user', User::count());
});

describe('audience', function () {
    it('can make announcement for all users', function () {
        User::factory()->count(5)->create();
        fillAnnouncementForm($this->component);

        $this->component
            ->set('announcement.audience', 'all')
            ->call('submit');

        expect($this->component->announcement->users)->toHaveCount(User::relevant()->count());
    });

    it('can make announcement for selected roles', function () {
        $role = Role::factory()->create();
        User::factory(3)->create();
        User::factory(5)->assignRole($role->id)->create();

        fillAnnouncementForm($this->component);

        $this->component
            ->set('announcement.audience', 'role')
            ->set('roleIds', [$role->id])
            ->call('submit');

        expect($this->component->announcement->users)->toHaveCount($role->users()->count());
    });

    it('can make announcement for selected users', function () {
        User::factory(10)->create();

        fillAnnouncementForm($this->component);
        $userIds = User::limit(3)->pluck('id')->toArray();

        $this->component
            ->set('announcement.audience', 'user')
            ->set('userIds', $userIds)
            ->call('submit');

        expect($this->component->announcement->users)->toHaveCount(3);
    });
});

describe('validation', function () {
    it('will validate form', function (){
        $this->component
            ->call('submit')
            ->assertHasErrors();
    });

    test('validation rules', function ($announcement, $errors){
        $setValue = fn($key, $value) => array_key_exists($key, $announcement) ? $announcement[$key] : $value;
        $announcement = [
            'subject' => $setValue('subject', 'announcement subject'),
            'body' => $setValue('body', 'announcement body'),
            'audience' => $setValue('audience', 'all'),
            'userIds' => $setValue('userIds', []),
            'roleIds' => $setValue('roleIds', []),
        ];

        $this->component
            ->set('announcement.subject', $announcement['subject'])
            ->set('announcement.body', $announcement['body'])
            ->set('announcement.audience', $announcement['audience'])
            ->set('userIds', $announcement['userIds'])
            ->set('roleIds', $announcement['roleIds'])
            ->call('submit')
            ->assertHasErrors($errors);
    })->with([
        'subject is required' => [
            ['subject' => null],
            'announcement.subject',
        ],
        'body is required' => [
            ['body' => null],
            'announcement.body',
        ],
        'audience is required' => [
            ['audience' => null],
            'announcement.audience',
        ],
        'userIds is required if audience is user ' => [
            ['audience' => 'user', 'userIds' => []],
            'userIds',
        ],
        'roleIds if audience is role' => [
            ['audience' => 'role', 'roleIds' => []],
            'roleIds',
        ],
    ]);
});

function fillAnnouncementForm($component): void
{
    $announcement = Announcement::factory()->make();
    $component
        ->set('announcement.subject', $announcement->subject)
        ->set('announcement.body', $announcement->body)
        ->set('announcement.audience', $announcement->audience);
}
