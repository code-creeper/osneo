<?php

namespace Database\Factories;

use App\Models\AttendanceSummary;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AttendanceSummaryFactory extends Factory
{
    protected $model = AttendanceSummary::class;

    public function definition(): array
    {
        $date = now()->subDays($this->faker->randomDigit());

        return [
            'date' => $date,
            'target_time' => $this->faker->numberBetween(6, 10) * 60,
            'working_time' => $this->faker->numberBetween(6, 10) * 60,
            'paid_time' => fn($attributes) => $attributes['leave'] || $attributes['holiday'] ? $attributes['target_time'] : 0,
            'manual_time' => $this->faker->randomDigit() * 60,
            'payout_time' => $this->faker->randomDigit() * 60,
            'overtime' => fn($attributes) => ($attributes['working_time'] + $attributes['paid_time'] + $attributes['manual_time'] ) - $attributes['target_time'],
            'leave' => $this->faker->boolean(),
            'off_day' => $this->faker->boolean(),
            'holiday' => $this->faker->boolean(),
            'weekend' => $date->isWeekend(),

            'user_id' => User::factory(),
        ];
    }
}
