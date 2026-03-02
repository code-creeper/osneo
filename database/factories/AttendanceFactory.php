<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'created_by' => null,
            'updated_by' => null,
            'date' => fn($attributes) => $attributes['checkin'],
            'checkin' => now()->hour($this->faker->numberBetween(6, 10)),
            'checkout' => fn($attributes) => Carbon::parse($attributes['checkin'])->copy()->addHours($this->faker->numberBetween(1, 8)),
            'comments' => $this->faker->sentence(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }

    public function active(): self
    {
        return $this->state(fn($attrs) => [
            'checkout' => null,
        ]);
    }

    public function checkin(int $hour, int $minute = 0): self
    {
        return $this->state(fn($attrs) => [
            'checkin' => now()->setTime($hour, $minute)
        ]);
    }

    public function date(string|Carbon $date): self
    {
        return $this->state(fn() => ['date' => $date])
            ->afterMaking(function (Attendance $attendance) {
                $attendance->checkin = $attendance->checkin?->setDateFrom($attendance->date);
                $attendance->checkout = $attendance->checkout?->setDateFrom($attendance->date);
            });
    }

    public function duration($duration): self
    {
        return $this->state(fn($attrs) => [
            'checkout' => $attrs['checkin']->copy()->add($duration)
        ]);
    }

    public function past(): self
    {
        return $this->state(fn($attrs) => [
            'checkin' => now()->subDays($this->faker->numberBetween(3, 30)),
        ]);
    }

    public function updatedByUser(): self
    {
        $user = User::factory()->create();

        return $this->state(fn($attrs) => [
            'user_id' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    public function createdByUser(): self
    {
        $user = User::factory()->create();

        return $this->state(fn($attrs) => [
            'user_id' => $user->id,
            'created_by' => $user->id,
        ]);
    }
}
