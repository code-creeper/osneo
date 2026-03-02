<?php

namespace Tests\RequestFactories;

use App\Models\User;
use Carbon\Carbon;
use Worksome\RequestFactories\RequestFactory;

class AttendanceFormRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
           'user_id' => User::factory(),
            'checkin' => now()->hour($this->faker->numberBetween(6, 10))->format('H:i'),
            'checkout' => fn($attributes) => Carbon::parse($attributes['checkin'])->copy()->addHours($this->faker->numberBetween(1, 8))->format('H:i'),
            'date' => fn($attributes) => Carbon::parse($attributes['checkin'])->toDateString(),
        ];
    }
}
