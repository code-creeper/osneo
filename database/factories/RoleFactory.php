<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        //$role = str($this->faker->unique()->jobTitle())->snake();
        $role = str($this->faker->unique()->text(10))->snake();

        return [
            'display_name' => fn($attrs) => str($attrs['name'])->title(),
            'name' => $role,
            'guard_name' => 'web',
            'primary' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
