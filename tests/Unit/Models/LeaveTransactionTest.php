<?php

use App\Models\LeaveTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('relations', function () {
    $transaction = LeaveTransaction::factory()->create();

    expect($transaction)
        ->user->exists()->toBeTrue()
        ->transactor->exists()->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Scopes
|--------------------------------------------------------------------------
*/

it('will get relevant', function () {

});

/*
|--------------------------------------------------------------------------
| Methods
|--------------------------------------------------------------------------
*/

it('will check if transaction is debit', function () {

});
