<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'lexoffice_id' => $this->faker->word(),
            'name' => $this->faker->name(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'is_customer' => $this->faker->boolean(),
            'is_supplier' => $this->faker->boolean(),
            'customer' => $this->faker->word(),
            'supplier' => null,
            'is_company' => $this->faker->company(),
            'description' => $this->faker->text(),
            'active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'manager_id' => null,
            'billing_address_id' => Address::factory(),
        ];
    }
}
