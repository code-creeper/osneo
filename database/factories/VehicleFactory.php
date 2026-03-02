<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $vehicle = $this->faker->vehicleArray;

        return [
            'license_plate' => $this->faker->regexify('[A-Z]{3}-[A-Z]{2} [0-9]{3}'),
            'ticket_number' => $this->faker->regexify('TKT-[0-9]{6}-[0-9]{6}'),
            'manufacturer' => $vehicle['brand'],
            'model' => $vehicle['model'],
            'status' => null,
            'created_at' => now(),
            'updated_at' => now(),

            'driver_id' => null,
        ];
    }
}
