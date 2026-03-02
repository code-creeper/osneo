<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleMaintenanceHistory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleMaintenanceHistoryFactory extends Factory
{
    protected $model = VehicleMaintenanceHistory::class;

    public function definition(): array
    {
        $yesNo = ['yes', 'no'];

        return [
            'mileage' => $this->faker->randomNumber(),
            'outside_condition' => $this->faker->randomElement(array_keys(config('constants.vehicle_conditions'))),
            'inside_condition' => $this->faker->randomElement(array_keys(config('constants.vehicle_conditions'))),
            'tank_level' => $this->faker->randomElement(array_keys(config('constants.tank_levels'))),
            'gas_card' => $this->faker->randomElement($yesNo),
            'safety_vest' => $this->faker->randomElement($yesNo),
            'first_aid_kit' => $this->faker->randomElement($yesNo),
            'first_aid_kit_expiry' => fn($attributes) => $attributes['first_aid_kit'] == 'yes' ? $this->faker->futureDateTimeThisYear : null,
            'craftsman_license' => $this->faker->randomElement($yesNo),
            'craftsman_license_expiry' => fn($attributes) => $attributes['craftsman_license'] == 'yes' ? $this->faker->futureDateTimeThisYear : null,
            'registration' => $this->faker->randomElement($yesNo),
            'service_booklet' => $this->faker->randomElement($yesNo),
            'front_left_tyre_profile' => $this->faker->randomElement($yesNo),
            'front_right_tyre_profile' => $this->faker->randomElement($yesNo),
            'back_left_tyre_profile' => $this->faker->randomElement($yesNo),
            'back_right_tyre_profile' => $this->faker->randomElement($yesNo),
            'next_maintenance_date' => $this->faker->futureDateTimeThisYear,
            'warning_triangle' => $this->faker->randomElement($yesNo),
            'mot_date' => $this->faker->futureDateTimeThisYear,
            'emission_sticker' => $this->faker->randomElement(array_keys(config('constants.emission_stickers'))),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'vehicle_id' => Vehicle::factory(),
            'user_id' => User::factory(),
        ];
    }
}
