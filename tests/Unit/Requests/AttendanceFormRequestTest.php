<?php

use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use Tests\RequestFactories\AttendanceFormRequestFactory;

use function Pest\Laravel\post;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
});

it('will validate attendance', function () {
    loginWithPermissions(Permissions::attendanceBasic());

    AttendanceFormRequestFactory::new()->fake();

    $response = post(route('attendances.store.manually'), [

    ]);

    $response->assertSessionHasErrors();
})->todo();
