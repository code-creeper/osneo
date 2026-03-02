<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'street' => $this->faker->streetName(),
            'zip_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'is_service_location' => $this->faker->boolean(),
            'heating_system' => $this->faker->word(),
            'last_maintained_on' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
