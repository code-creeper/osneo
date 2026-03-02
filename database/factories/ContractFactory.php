<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Contract;
use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition(): array
    {
        return [
            'lexoffice_id' => $this->faker->uuid(),
            'name' => $this->faker->name(),
            'services' => $this->getServices(),
            'sections' => config('lexoffice.defaults'),
            'is_offer' => 1,
            'lexoffice_payload' => fixture('Lexoffice/get_quotation.json'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'ticket_id' => Ticket::factory(),
            'contact_id' => Contact::factory(),
        ];
    }

    public function getServices(): array
    {
        $count = $this->faker->numberBetween(2, 5);
        $services = [];

        foreach (range(0, $count) as $index){
            $service = Service::factory()->create();
            $size = $service->sizes[0];

            $services[$index] = [
                'category_id' => $service->service_category_id,
                'service_id' => $service->id,
                'service_name' => $service->name,
                'size_id' => 0,
                'price' => $size['price'],
                'size' => $size['name'],
                'unit' => $service->unit,
                'description' => $service->description,
            ];
        }

        return $services;
    }

    public function synced(): self
    {
        return $this->state(fn($attrs) => [
            'lexoffice_id' => $this->faker->uuid(),
            'lexoffice_payload' => json_decode(fixture('Lexoffice/get_quotation.json'), true),
        ]);
    }

    public function notSynced(): self
    {
        return $this->state(fn($attrs) => [
            'lexoffice_id' => $this->faker->uuid(),
            'lexoffice_payload' => null,
        ]);
    }

    public function withoutLexoffice(): self
    {
        return $this->state(fn($attrs) => [
            'lexoffice_id' => null,
            'lexoffice_payload' => null,
        ]);
    }
}
