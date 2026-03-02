<?php

namespace Database\Factories;

use App\Models\LeaveTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class LeaveTransactionFactory extends Factory
{
    protected $model = LeaveTransaction::class;

    public function definition(): array
    {
        return [
            'transacted_on' => now()->addDays($this->faker->numberBetween(-10, 10)),
            'amount' => $this->faker->numberBetween(1, 10),
            'comments' => $this->faker->word(),
            'created_at' => now(),
            'updated_at' => now(),

            'user_id' => User::factory(),
            'transacted_by' => User::factory(),
        ];
    }
}
