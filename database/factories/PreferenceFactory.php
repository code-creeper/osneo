<?php

namespace Database\Factories;

use App\Models\Preference;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PreferenceFactory extends Factory
{
    protected $model = Preference::class;

    public function definition(): array
    {
        $preferences = array_keys(config('preferences'));

        $values = array(
            'allowed_document_types' => [1, 2, 3],
            'leave_increment_start_year' => $this->faker->year,
            'leave_increment_per_year' => $this->faker->randomDigit(),
        );

        return [
            'user_id' => User::factory(),
            'role_id' => Role::factory(),
            'name' => $this->faker->randomElement($preferences),
            'value' => fn($attrs) => $values[$attrs['name']],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
