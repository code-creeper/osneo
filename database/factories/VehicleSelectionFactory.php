<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleSelection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleSelectionFactory extends Factory
{
    protected $model = VehicleSelection::class;

    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'user_id' => User::factory(),
            'created_at' => $this->faker->dateTimeThisHour(),
        ];
    }
}
