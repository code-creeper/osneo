<?php

use App\Models\Constant;
use Illuminate\Foundation\Testing\RefreshDatabase;


uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Scopes
|--------------------------------------------------------------------------
*/

//scopeGroup
it('can get constants by group', function () {
    Constant::truncate();
    Constant::factory(3)->create();
    Constant::factory(2)->create(['group' => 'test_group']);

    expect(Constant::count())->toBe(5)
        ->and(Constant::group('test_group')->count())->toBe(2);
});
