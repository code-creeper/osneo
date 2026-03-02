<?php

namespace Database\Factories;

use App\Models\Employment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EmploymentFactory extends Factory
{
    protected $model = Employment::class;

    public function definition(): array
    {
        return [
            'employment_type' => $this->faker->randomElement(['weekly', 'hourly']),
            'weekly_target_time' => function ($attributes) {
                $hours = $attributes['employment_type'] === 'weekly'
                    ? $this->faker->numberBetween(6, 10)
                    : $this->faker->numberBetween(150, 180);

                return $hours * 60;
            },

            'off_days' => ['saturday', 'sunday'],
            'hourly_rate' => $this->faker->numberBetween(5, 60),

            // we keep the employments to start in few months past and end in few months in future
            // so when creating leaves, attendances we don't have any problems with employments
            'started_on' => now()->subMonths($this->faker->numberBetween(3, 15)),
            'ended_on' => null,

            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }

    public function weekly(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'employment_type' => 'weekly',
            ];
        });
    }

    public function hourly(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'employment_type' => 'hourly',
            ];
        });
    }

    public function current(): self
    {
        return $this->state(fn($attrs) => [
            'ended_on' => null,
        ]);
    }

    public function active(): self
    {
        return $this->current();
    }

    public function fullWeek(): self
    {
        return $this->state(fn($attrs) => [
            'off_days' => [],
        ]);
    }

    public function weekdays(): self
    {
        return $this->state(fn($attrs) => [
            'off_days' => ['saturday', 'sunday'],
        ]);
    }

    public function past(): self
    {
        return $this->state(fn($attrs) => [
            'started_on' => now()->subYears(2),
            'ended_on' => now()->subYear(),
        ]);
    }
}
