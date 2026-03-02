<?php


use App\Models\ManualEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('boot', function (){
    it('will update attendance summary on create')->todo();
    it('will update attendance summary on update')->todo();
    it('will update attendance summary on delete')->todo();
});

test('relations', function () {
    $entry = ManualEntry::factory()
        ->forUser()
        ->forAdmin()
        ->create();

    expect($entry)
        ->user->exists()->toBeTrue()
        ->admin->exists()->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Attributes
|--------------------------------------------------------------------------
*/

it('can get minutes from duration', function ($duration, $minutes) {
    $entry = ManualEntry::factory()->create(['duration' => $duration]);
    expect($entry->minutes)->toBe($minutes);
})->with([
    [30, 30], [60, 0], [70, 10], [120, 0], [130, 10],
]);

it('can get hours from duration', function ($duration, $hours) {
    $entry = ManualEntry::factory()->create(['duration' => $duration]);
    expect($entry->hours)->toBe($hours);
})->with([
    [30, 0], [60, 1], [70, 1], [120, 2], [130, 2],
]);

/*
|--------------------------------------------------------------------------
| Scopes
|--------------------------------------------------------------------------
*/

it('can get break entries', function () {
    ManualEntry::factory(3)->break()->create();
    ManualEntry::factory(2)->attendance()->create();

    expect(ManualEntry::break()->count())->toBe(3);
});

it('can get attendance entries', function () {
    ManualEntry::factory(3)->attendance()->create();
    ManualEntry::factory(2)->break()->create();

    expect(ManualEntry::attendance()->count())->toBe(3);
});

it('can get payout entries', function () {
    ManualEntry::factory(3)->payout()->create();
    ManualEntry::factory(2)->create(['payout' => 0]);

    expect(ManualEntry::payout()->count())->toBe(3);
});
