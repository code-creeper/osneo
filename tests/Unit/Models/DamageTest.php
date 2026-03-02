<?php

use App\Models\Damage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('relations', function () {
    $damage = Damage::factory()
        ->forStatus()
        ->forUser()
        ->create();

    expect($damage)
        ->status->exists()->toBeTrue()
        ->user->exists()->toBeTrue();
});

//scopeRelevant
it('will get relevant damages', function () {
    login();
    Damage::factory()->count(3)->create([
        'user_id' => auth()->id()
    ]);

    expect(Damage::count())->toBe(3);

    loginWithPermissions('view all damages');
    Damage::factory(2)->create();

    expect(Damage::count())->toBe(5);
});
