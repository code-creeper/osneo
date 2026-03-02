<?php

namespace Database\Factories;

use App\Models\ManualEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ManualEntryFactory extends Factory
{
    protected $model = ManualEntry::class;

    public function definition(): array
    {
        return [
            'date' => Carbon::now(),
            'duration' => $this->faker->randomDigitNotZero() * 60 * $this->faker->randomElement([-1, 1]),
            'comments' => $this->faker->sentence(),
            'payout' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
            'logged_by' => User::factory(),
        ];
    }

    public function payout(): Factory
    {
        return $this->state(fn($attrs) => ['payout' => 1]);
    }

    public function attendance(): Factory
    {
        return $this->state(function ($attrs) {
            return [
                'duration' => abs($attrs['duration']),
                'payout' => 0,
            ];
        });
    }

    public function break(): Factory
    {
        return $this->state(fn($attrs) => [
            'duration' => abs($attrs['duration']) * -1,
            'payout' => 0,
        ]);
    }

}
