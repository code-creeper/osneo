<?php

use App\Livewire\Forms\ManualEntryForm;
use App\Models\ManualEntry;
use App\Models\User;
use App\Notifications\ManualEntryNotification;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(ManualEntryForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

it('can submit form', function ($user) {

    $this->component
        ->set('duration', 1)
        ->set('entry.user_id', $user->id)
        ->set('date', today()->date())
        ->set('entry.comments', 'some comments')
        ->set('entry.payout', 1)
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Manual entry saved');

    assertDatabaseHas(ManualEntry::class, [
        'user_id' => $user->id,
        'date' => today(),
        'duration' => 60,
        'payout' => 1,
        'comments' => 'some comments'
    ]);

    if ($user->id !== auth()->id()){
        Notification::assertSentTo($user, ManualEntryNotification::class);
    }

})->with([
    'self' => fn() => auth()->user(),
    'other' => fn() => User::factory()->create(),
]);

describe('validation', function (){
    beforeEach(fn() => $this->data = [
        'duration' => 1,
        'entry.user_id' => auth()->id(),
        'date' => today()->date(),
    ]);

    test('the user_id is required', function (){
        $this->component
            ->set(Arr::except($this->data, 'entry.user_id'))
            ->call('submit')
            ->assertHasErrors('entry.user_id');
    });

    test('the date is required', function (){
        $this->component
            ->set(Arr::except($this->data, 'date'))
            ->call('submit')
            ->assertHasErrors('entry.date');
    });

    test('the duration is required', function (){
        $this->component
            ->set(Arr::except($this->data, 'duration'))
            ->call('submit')
            ->assertHasErrors('entry.duration');
    });

    test('the duration should not be zero', function (){
        $this->component
            ->set($this->data)
            ->set('duration', 0)
            ->call('submit')
            ->assertHasErrors('entry.duration');
    });
});
