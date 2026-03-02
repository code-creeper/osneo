<?php

use App\Console\Commands\UpdateInsuranceClaimsCommand;
use App\Enums\InsuranceClaimStatus;
use App\Models\InsuranceClaim;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Symfony\Component\Console\Command\Command;

use function Pest\Laravel\artisan;
use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);


function assertClaimStatus(&$claim, $status): void
{
    artisan(UpdateInsuranceClaimsCommand::class)->assertExitCode(Command::SUCCESS);
    $claim->refresh();
    expect($claim->status->value)->toBe($status->value);
}

it('can update claim statuses', function () {
    testTime()->freeze();

    $claim = InsuranceClaim::factory([
        'status' => InsuranceClaimStatus::UNCONFIRMED,
        'attempt' => 1,
    ])->create();

    // it will not update status before 2 weeks
    assertClaimStatus($claim, InsuranceClaimStatus::UNCONFIRMED);

    testTime()->addWeeks(2);
    assertClaimStatus($claim,InsuranceClaimStatus::OPEN);
});

it('will not update claim status if attempt is not 1', function ($attempt) {
    testTime()->freeze();

    $claim = InsuranceClaim::factory([
        'status' => InsuranceClaimStatus::UNCONFIRMED,
        'last_requested_on' => now()->subWeeks(2),
        'attempt' => $attempt,
    ])->create();
    assertClaimStatus($claim, InsuranceClaimStatus::UNCONFIRMED);
})->with([
    0, 2,
]);
