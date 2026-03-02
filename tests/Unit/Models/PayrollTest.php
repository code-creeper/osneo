<?php

use App\Enums\PayrollStatus;
use App\Models\LeaveTransaction;
use App\Models\ManualEntry;
use App\Models\Payroll;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

test('relations', function () {
    $payroll = Payroll::factory()->forUser()->create();

    expect($payroll->user->exists())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Attributes
|--------------------------------------------------------------------------
*/

//salary
it('can get salary', function () {
    $payroll = Payroll::factory()->create([
        'hourly_rate' => 5,
        'working_hours' => 5
    ]);

    expect($payroll->salary)->toBe(25);
});

//leavePayout
it('can get leave payout', function () {
    $payroll = Payroll::factory()->create([
        'hourly_rate' => 5,
        'leaves_balance' => 5,
        'target_hours' => 5,
    ]);

    expect($payroll->leave_payout)->toBe(125);
});

//grossTotal
it('can get gross total amount of payroll', function () {
    $overtimes = array(
        ['hours' => 5, 'hourly_rate' => 5], // 25
        ['hours' => 2, 'hourly_rate' => 10] // 20
    );

    $surcharges = array(
        ['description' => 'text', 'amount' => 5, 'tax' => 'gross'],
        ['description' => 'text', 'amount' => 10, 'tax' => 'net'],
    );

    $payroll = Payroll::factory()->create([
        'hourly_rate' => 5,
        'working_hours' => 5,
        'leaves_balance' => 5,
        'target_hours' => 5,
        'overtimes' => $overtimes,
        'surcharges' => $surcharges
    ]);

    // salary: 25, leave_payout: 125, overtimes: 45, surcharges: 5

    expect($payroll->gross_total)->toBe(200);
});

//netTotal
it('can get net total amount of payroll', function () {
    $surcharges = array(
        ['description' => 'text', 'amount' => 5, 'tax' => 'net'],
        ['description' => 'text', 'amount' => 15, 'tax' => 'net'],
        ['description' => 'text', 'amount' => 10, 'tax' => 'gross'],
    );

    $payroll = Payroll::factory()->create([
        'surcharges' => $surcharges
    ]);

    expect($payroll->net_total)->toBe(20);
});

//month
it('can get month of payroll', function () {
    $payroll = Payroll::factory()->create(['date' => '2023-06-01']);
    expect($payroll->month)->toBe('2023-06');
});

//year
it('can get year of payroll', function () {
    $payroll = Payroll::factory()->create(['date' => '2023-06-01']);
    expect($payroll->year)->toBe(2023);
});

/*
|--------------------------------------------------------------------------
| Methods
|--------------------------------------------------------------------------
*/

//process
it('can process payroll', function () {
    $overtimes = array(
        ['hours' => 1, 'hourly_rate' => 5], // 25
        ['hours' => 2, 'hourly_rate' => 10] // 20
    );

    $surcharges = array(
        ['description' => 'text', 'amount' => 5, 'tax' => 'gross'],
        ['description' => 'text', 'amount' => 10, 'tax' => 'net'],
    );

    $payroll = Payroll::factory()->create([
        'hourly_rate' => 5,
        'working_hours' => 5,
        'leaves_balance' => 5,
        'target_hours' => 5,
        'overtimes' => $overtimes,
        'surcharges' => $surcharges
    ]);

    $payroll->process();

    expect($payroll->status)->toBe(PayrollStatus::COMPLETED);

    assertDatabaseHas(LeaveTransaction::class, [
        'user_id' => $payroll->user_id,
        'amount' => -5
    ]);

    assertDatabaseHas(ManualEntry::class, [
        'user_id' => $payroll->user_id,
        'duration' => 120,
        'payout' => 1
    ]);

    assertDatabaseHas(ManualEntry::class, [
        'user_id' => $payroll->user_id,
        'duration' => 60,
        'payout' => 1
    ]);
});
