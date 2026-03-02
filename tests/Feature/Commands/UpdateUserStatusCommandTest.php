<?php

use App\Console\Commands\UpdateUserStatusCommand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Symfony\Component\Console\Command\Command;

use function Pest\Laravel\artisan;
use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);

it('can update users status', function ($column, $active) {
    testTime()->freeze();
    User::forceTruncate();

    User::factory([
        $column => now()->addDays(5),
        'active' => $active
    ])
        ->count(2)
        ->sequence(
            [$column => now()->addDays(5)],
            [$column => now()->addDays(6)]
        )
        ->create();

    artisan(UpdateUserStatusCommand::class)->assertExitCode(Command::SUCCESS);
    expect(User::withoutGlobalScopes()->whereActive($active)->count())->toBe(2);

    testTime()->addDays(6);
    artisan(UpdateUserStatusCommand::class)->assertExitCode(Command::SUCCESS);
    expect(User::withoutGlobalScopes()->whereActive($active)->count())->toBe(0);

})->with([
    'activate' => ['activate_on', 0],
    'deactivate' => ['deactivate_on', 1]
]);
