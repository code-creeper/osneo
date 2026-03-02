<?php

namespace Database\Factories;

use App\Contracts\Modifiable;
use App\Enums\ModificationType;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Modification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ModificationFactory extends Factory
{
    protected $model = Modification::class;

    public function definition(): array
    {
        $modifiables = [Leave::class, Attendance::class];

        return [
            'modifiable_type' => $this->faker->randomElement($modifiables),
            'modifiable_id' => fn($attributes) => $attributes['modifiable_type']::factory(),
            'type' => ModificationType::Edit,
            'approved_by' => null,
            'approved_at' => null,
            'comments' => $this->faker->sentence(),
            'remarks' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => auth()->id() ?? User::factory(),
        ];
    }

    public function approved(): self
    {
        return $this->state(fn($attrs) => [
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeThisHour(),
        ]);
    }

    public function creation(): self
    {
        return $this->state(fn($attrs) => [
            'type' => ModificationType::Create,
            'modifiable_id' => 0,
        ]);
    }

    public function attendance(ModificationType $modificationType = ModificationType::Create): self
    {
        return $this->state(function ($attrs) use ($modificationType) {
            $attendance = null;

            if ($modificationType != ModificationType::Create){
                $attendance = Attendance::factory()->create([
                    'user_id' => $attrs['user_id']
                ]);
            }

            return [
                'modifiable_type' => Attendance::class,
                'modifiable_id' => $attendance ? $attendance->id : 0,
                'type' => $modificationType,
            ];
        });
    }

    public function leave(ModificationType $modificationType = ModificationType::Create): self
    {
        $leave = null;

        if ($modificationType != ModificationType::Create){
            $leave = Leave::factory()->create();
        }

        return $this->state(fn($attrs) => [
            'modifiable_type' => Leave::class,
            'modifiable_id' => $leave ? $leave->id : 0,
            'type' => $modificationType,
            'user_id' => $leave ? $leave->user_id : $attrs['user_id'],
        ]);
    }
}
