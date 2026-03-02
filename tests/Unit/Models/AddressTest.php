<?php

use App\Models\Address;

use Illuminate\Foundation\Testing\RefreshDatabase;


uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Methods
|--------------------------------------------------------------------------
*/

//fullAddress
it('can get full address', function () {
    $address = Address::factory()->create([
        'street' => 'st 1',
        'city' => 'city',
        'zip_code' => '123'
    ]);

    expect($address)->fullAddress()->toBe("st 1, city 123");
});
