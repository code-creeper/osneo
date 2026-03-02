<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDriverHistory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleDriverHistoryFactory extends Factory
{
    protected $model = VehicleDriverHistory::class;

    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'taken_at' => $this->faker->dateTimeThisHour(),
            'handed_over_at' => fn($attrs) => $attrs['taken_at']->addHours($this->faker->numberBetween(8, 24)),

            'driver_id' => User::factory(),
        ];
    }

    public function taken(): Factory
    {
        return $this->state(fn($attrs) => [
            'handed_over_at' => null
        ]);
    }
}
