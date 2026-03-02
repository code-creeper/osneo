<?php

namespace Database\Factories;

use App\Models\Constant;
use App\Models\Damage;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DamageFactory extends Factory
{
    protected $model = Damage::class;

    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'part' => $this->faker->word(),
            'type' => $this->faker->word(),
            'description' => $this->faker->text(),
            'notes' => $this->faker->sentence(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
            'status_id' => 1,
        ];
    }
}
