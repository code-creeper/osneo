<?php

namespace Database\Factories;

use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentTypeFactory extends Factory
{
    protected $model = DocumentType::class;

    public function definition(): array
    {
        $key = $this->faker->randomElement(array_keys(config('constants.document_types')));
        $name = config('constants.document_types')[$key];

        return [
            'name' => $name,
            'key' => $key,
            'lexoffice' => DocumentType::isLexoffice($key),
            'subscriber_ids' => null,
        ];
    }
}
