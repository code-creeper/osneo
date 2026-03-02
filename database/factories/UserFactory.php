<?php

namespace Database\Factories;

use App\Models\Employment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => Carbon::now(),
            'password' => bcrypt('password'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'avatar' => null,
            'dob' => $this->faker->dateTimeBetween('1990-01-01', '2012-12-31'),
            'health_insurance_number' => $this->faker->word(),
            'ssn' => $this->faker->word(),
            'birth_name' => $this->faker->name(),
            'birthplace' => $this->faker->city(),
            'active' => 1,
            'activate_on' => null,
            'deactivate_on' => null,
            'remember_token' => Str::random(10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'role_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn($attrs) => [
            'email_verified_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn($attrs) => [
            'active' => 0,
        ]);
    }

    public function configure(): self
    {
        return $this->afterCreating(function (User $user) {
            if (!$user->employments()->exists()) {
                Employment::factory()
                    ->active()
                    ->weekdays()
                    ->create([
                        'user_id' => $user->id,
                    ]);
            }
        });
    }

    public function assignRole(int $role): Factory
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole($role));
    }
}
