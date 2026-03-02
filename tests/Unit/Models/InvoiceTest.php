<?php

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('boot', function (){
    it('it will dispatch job to send invoice to creditreform on created', function () {

    });
});

test('relations', function () {
    $invoice = Invoice::factory()->forDocument()->create();
    expect($invoice->document->exists())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Attributes
|--------------------------------------------------------------------------
*/

it('can get voucher', function () {

})->todo();

it('can get payment', function () {

})->todo();

it('can get contact', function () {

})->todo();

it('can get customer', function () {

})->todo();

it('can get person', function () {

})->todo();

it('can get creditreform payload', function () {

})->todo();

it('can get total gross amount', function () {

})->todo();

it('can get total open amount', function () {

})->todo();

it('can get status', function () {

})->todo();

/*
|--------------------------------------------------------------------------
| Methods
|--------------------------------------------------------------------------
*/

it('can check if invoice is voucher', function () {

})->todo();
