<?php

use App\Models\Employment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('relations', function () {
    $employment = Employment::factory()->create();
    expect($employment->user->exists())->toBeTrue();
});

//period
it('will return employment period', function () {
    $employment = Employment::factory()->create([
        'started_on' => '01-01-2022',
        'ended_on' => '01-12-2022',
    ]);

    $employment2 = Employment::factory()->active()->create([
        'started_on' => '01-01-2022',
    ]);

    expect($employment->period)->toBe('01.01.2022 - 01.12.2022')
        ->and($employment2->period)->toBe('01.01.2022 - Present');
});

//scopeForDate
describe('scopeForDate', function (){
    it('will get employments for given date', function ($date, $count) {
        Employment::forceTruncate();
        $employment = Employment::factory([
            'started_on' => '01-01-2022',
            'ended_on' => '31-12-2022',
        ]);

        User::factory()->has($employment, 'employments')->create();

        expect(Employment::forDate($date)->count())->toBe($count);
    })->with([
        ['31-12-2021', 0],
        ['01-01-2022', 1],
        ['01-02-2022', 1],
        ['31-12-2022', 1],
        ['01-01-2023', 0],
    ]);

    it('will get employments for given date if employment is still active', function () {
        Employment::forceTruncate();
        $employment = Employment::factory([
            'started_on' => '01-01-2022',
        ])->active();

        User::factory()->has($employment, 'employments')->create();

        expect(Employment::forDate('01-01-2023')->count())->toBe(1);
    });
});

//updateAttendanceSummary
it('can update attendance summaries', function () {

})->todo();
