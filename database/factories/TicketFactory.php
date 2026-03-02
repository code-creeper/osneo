<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->regexify('TKT-[0-9]{6}-[0-9]{6}'),
            'synced' => $this->faker->boolean(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function synced(): Factory
    {
        return $this->state(fn($attrs) => ['synced' => 1]);
    }
}
