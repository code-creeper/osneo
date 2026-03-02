<?php

namespace Database\Factories;

use App\Models\LeaveReason;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class LeaveReasonFactory extends Factory
{
    protected $model = LeaveReason::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'color' => $this->faker->hexColor(),
            'paid' => 1,
            'deductible' => 0,
        ];
    }

    public function paid(): self
    {
        return $this->state(fn($attrs) => ['paid' => 1]);
    }

    public function unpaid(): self
    {
        return $this->state(fn($attrs) => ['paid' => 0]);
    }

    public function deductible(): self
    {
        return $this->state(fn($attrs) => [
            'deductible' => 1,
            'paid' => 1
        ]);
    }

    public function vacation(): self
    {
        return $this->deductible();
    }
}
