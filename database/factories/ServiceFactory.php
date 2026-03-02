<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'unit' => $this->faker->word(),
            'sizes' => $this->getSizes(),
            'description' => $this->faker->text(),

            'service_category_id' => ServiceCategory::factory(),
        ];
    }

    public function getSizes(): array
    {
        $count = $this->faker->numberBetween(2, 5);
        $sizes = [];

        foreach (range(0, $count) as $index){
            $sizes[$index] = [
                'name' => $this->faker->unique()->word,
                'price' => number_format($this->faker->randomFloat(2, 10, 1000), 2)
            ];
        }

        return $sizes;
    }
}
