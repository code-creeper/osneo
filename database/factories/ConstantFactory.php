<?php

namespace Database\Factories;

use App\Models\Constant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ConstantFactory extends Factory
{
    protected $model = Constant::class;

    public function definition(): array
    {
        return [
            'group' => $this->faker->word(),
            'key' => $this->faker->word(),
            'value' => $this->faker->word(),
            'fields' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
