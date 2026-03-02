<?php

namespace App\Console\Commands;

use App\Enums\InsuranceClaimStatus;
use App\Models\InsuranceClaim;
use Illuminate\Console\Command;

class UpdateInsuranceClaimsCommand extends Command
{
    protected $signature = 'leave:update-claims';

    protected $description = 'Update status of claims to open which were rejected 2 weeks ago';

    public function handle(): void
    {
        $count = InsuranceClaim::where('status', InsuranceClaimStatus::UNCONFIRMED)
            ->where('attempt', 1)
            ->whereDate('last_requested_on', '<=', today()->subWeeks(2))
            ->update([
               'status' => InsuranceClaimStatus::OPEN
            ]);
    }
}
