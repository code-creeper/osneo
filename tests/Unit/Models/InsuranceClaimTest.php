<?php

use App\Models\InsuranceClaim;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('will generate claim number when created', function () {

})->todo();

test('relations', function (){
    $claim = InsuranceClaim::factory()
        ->forUser()
        ->forLeave()
        ->create();

    expect($claim)
        ->user->exists()->toBeTrue()
        ->leave->exists()->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Methods
|--------------------------------------------------------------------------
*/

it('can generate claim number', function () {
    $claim = InsuranceClaim::factory()->create();

    expect($claim->claim_number)
        ->toStartWith('SL-');
})->todo();

it('can check if insurance claim accepts documents', function (){

})->todo();

it('can check if insurance claim  is processed', function (){

})->todo();

it('can check if insurance claim status is waiting', function (){

})->todo();

it('can check if insurance claim status is open', function (){

})->todo();

it('can check if insurance claim status is rejected', function (){

})->todo();

it('can check if insurance claim status is confirmed', function (){

})->todo();

it('can check if insurance claim status is unconfirmed', function (){

})->todo();
