<?php

use App\Models\Preference;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('have value accessor', function (){

})->todo();

test('have value mutator', function (){
    $preference = Preference::create([
        'name' => 'allowed_document_types',
        'value' => '1',
    ]);

    expect($preference->value)->toBeInt();
})->todo();
