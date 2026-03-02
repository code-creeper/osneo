<?php

namespace Database\Factories;

use App\Enums\InsuranceClaimStatus;
use App\Models\InsuranceClaim;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InsuranceClaimFactory extends Factory
{
    protected $model = InsuranceClaim::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'leave_id' => Leave::factory(),
            'claim_number' => null,
            'attempt' => $this->faker->randomElement([0, 1, 2]),
            'last_requested_on' => Carbon::now(),
            'status' => $this->faker->randomElement(InsuranceClaimStatus::cases()),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function processed(): Factory
    {
        return $this->state(fn($attrs) => [
            'status' => $this->faker->randomElement([
                InsuranceClaimStatus::REJECTED,
                InsuranceClaimStatus::DONE,
            ]),
        ]);
    }
}
