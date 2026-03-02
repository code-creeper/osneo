<?php

namespace Database\Factories;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayrollFactory extends Factory
{
    protected $model = Payroll::class;

    public function definition(): array
    {
        return [
            'date' => Carbon::now(),
            'hourly_rate' => $this->faker->numberBetween(5, 25),
            'target_hours' => $this->faker->numberBetween(5, 10),
            'working_hours' => $this->faker->randomFloat(),
            'overtimes' => [],
            'surcharges' => [],
            'leaves_balance' => $this->faker->numberBetween(0, 20),
            'information' => null,
            'notes' => null,
            'vacation' => null,
            'leaves' => null,
            'status' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }

    public function overtimes(): self
    {
        return $this->state(fn($attrs) => [
            'overtimes' => [
                'hours' => 5,
                'hourly_rate' => 5,
            ],
        ]);
    }

    public function surcharges(): self
    {
        return $this->state(fn($attrs) => [
            'surcharges' => [
                'description' => 'description',
                'amount' => 5,
                'tax' => $this->faker->randomElement(['gross', 'net']),
            ],
        ]);
    }
}
