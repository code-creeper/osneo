<?php

use App\Livewire\Modals\EmploymentManagement;
use App\Models\Employment;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    login();
    $this->component = Livewire::test(EmploymentManagement::class, ['user' => auth()->user()]);
});

it('can render modal', function () {
    $this->component->assertSuccessful();
});

it('can delete employment', function () {
    $employment = user()->employment;

    $this->component
        ->call('delete')
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Employment deleted');

    assertDatabaseMissing(Employment::class, [
        'id' => $employment->id
    ]);
});

describe('select employment', function (){
    it('will open new employment form if no employment provided', function (){
        $this->component->call('selectEmployment');

        expect($this->component)
            ->started_on->toBe(now()->date())
            ->ended_on->toBeNull()
            ->off_days->toBe([])
            ->creatingNewEmployment->toBeTrue();
    });

    it('can select employment', function () {
        $employment = Employment::factory()->create([
            'started_on' => '2024-01-01',
            'off_days' => ['sunday']
        ]);
        $this->component->call('selectEmployment', employment: $employment);

        expect($this->component)
            ->started_on->toBe('01.01.2024')
            ->ended_on->toBeNull()
            ->off_days->toBe(['sunday']);
    });
});

it('can open new employment form', function () {
    $this->component->call('openNewForm');

    expect($this->component)
        ->started_on->toBe(now()->date())
        ->ended_on->toBeNull()
        ->off_days->toBe([])
        ->creatingNewEmployment->toBeTrue()
        ->selectedEmployment->toBeInstanceOf(Employment::class)
        ->selectedEmployment->user_id->toBe(auth()->id())
        ->selectedEmployment->employment_type->toBe('weekly')
        ->selectedEmployment->off_days->toBe([])
        ->selectedEmployment->weekly_target_time->toBeNull()
        ->selectedEmployment->monthly_target_time->toBeNull();
});

describe('form validation', function (){
    it('will not submit empty form', function () {
        $this->component
            ->call('selectEmployment')
            ->call('submit')
            ->assertHasErrors();
    });

    test('validation rules', function ($employment, $errors){
        $setValue = fn($key, $value) => array_key_exists($key, $employment) ? $employment[$key] : $value;
        $employment = [
            'user_id' => $setValue('user_id', auth()->id()),
            'started_on' => $setValue('started_on', '2024-01-01'),
            'ended_on' => $setValue('ended_on', '2024-12-01'),
            'employment_type' => $setValue('employment_type', 'weekly'),
            'weekly_target_time' => $setValue('weekly_target_time', 2400),
            'monthly_target_time' => $setValue('monthly_target_time', null),
            'hourly_rate' => $setValue('hourly_rate', 5.5),
            'off_days' => $setValue('off_days', ['saturday', 'sunday']),
        ];

        $this->component
            ->call('openNewForm')
            ->set('selectedEmployment.user_id', $employment['user_id'])
            ->set('started_on', $employment['started_on'])
            ->set('ended_on', $employment['ended_on'])
            ->set('selectedEmployment.employment_type', $employment['employment_type'])
            ->set('selectedEmployment.weekly_target_time', $employment['weekly_target_time'])
            ->set('selectedEmployment.monthly_target_time', $employment['monthly_target_time'])
            ->set('selectedEmployment.hourly_rate', $employment['hourly_rate'])
            ->set('off_days', $employment['off_days'])
            ->call('submit')
            ->assertHasErrors($errors);
    })->with([
        'user_id is required' => [
            ['user_id' => null],
            'selectedEmployment.user_id',
        ],
        'weekly_target_time is required' => [
            ['weekly_target_time' => null],
            'selectedEmployment.weekly_target_time',
        ],
        'monthly_target_time is required' => [
            ['employment_type' => 'hourly', 'monthly_target_time' => null],
            'selectedEmployment.monthly_target_time',
        ],
        'employment_type is required' => [
            ['employment_type' => null],
            'selectedEmployment.employment_type',
        ],
        'hourly_rate is required' => [
            ['hourly_rate' => null],
            'selectedEmployment.hourly_rate',
        ],
        'hourly_rate should be numeric' => [
            ['hourly_rate' => 'abc'],
            'selectedEmployment.hourly_rate',
        ],
        'started_on is required' => [
            ['started_on' => null],
            'started_on',
        ],
    ]);

});

it('can submit form', function ($employment) {
    $setValue = fn($key, $value) => array_key_exists($key, $employment) ? $employment[$key] : $value;
    $employment = [
        'started_on' => $setValue('started_on', '2024-01-01'),
        'ended_on' => $setValue('ended_on', '2024-12-01'),
        'employment_type' => $setValue('employment_type', 'weekly'),
        'weekly_target_time' => $setValue('weekly_target_time', 2400),
        'monthly_target_time' => $setValue('monthly_target_time', null),
        'hourly_rate' => $setValue('hourly_rate', 5.5),
        'off_days' => $setValue('off_days', ['saturday', 'sunday']),
    ];

    $this->component
        ->call('openNewForm')
        ->set('started_on', $employment['started_on'])
        ->set('ended_on', $employment['ended_on'])
        ->set('selectedEmployment.employment_type', $employment['employment_type'])
        ->set('selectedEmployment.weekly_target_time', $employment['weekly_target_time'])
        ->set('selectedEmployment.monthly_target_time', $employment['monthly_target_time'])
        ->set('selectedEmployment.hourly_rate', $employment['hourly_rate'])
        ->set('off_days', $employment['off_days'])
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Employment updated');

    $databaseRecord = array_merge($employment, [
        'user_id' => auth()->id(),
        'off_days' => $this->castAsJson($employment['off_days'])
    ]);


    assertDatabaseHas(Employment::class, $databaseRecord);
})->with([
    'employment with start and end date' => fn() => [],
    'employment without end date' => fn() => ['ended_on' => null,],
    'employment with hourly employment type' => fn() => [
        'employment_type' => 'hourly',
        'monthly_target_time' => 9600,
    ],
]);

