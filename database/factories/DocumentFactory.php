<?php

namespace Database\Factories;

use App\Enums\DocumentSource;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Storage;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->uuid().".pdf",
            'sorted_path' => null,
            'source' => null,
            'lexoffice_id' => null,
            'lexoffice_voucher_type' => null,
            'creditreform_id' => null,
            'properties' => null,
            'status' => 0,
            'sorted_on' => null,
            'created_at' => now(),
            'updated_at' => now(),

            'documentable_type' => null,
            'documentable_id' => null,
            'ticket_id' => Ticket::factory(),
            'uploaded_by' => null,
            'sorted_by' => null,
            'document_type_id' => null,
        ];
    }

    public function sorted(): self
    {
        return $this->state(fn($attrs) => [
            'source' => $this->faker->randomElement(DocumentSource::toArray()),
            'document_type_id' => DocumentType::factory(),
            'sorted_by' => User::factory(),
            'sorted_on' => now(),
            'status' => 2,
            //todo:: improve sorted state
        ])->afterCreating(function (Document $document) {
            // if sorted_path is provided manually, don't generate name
            if ($document->sorted_path){
                return;
            }

            $document->generateName();
        });
    }

    public function lexoffice(): self
    {
        return $this->state(fn($attrs) => ['lexoffice_id' => $this->faker->uuid]);
    }

    public function withFile()
    {
        return $this->state(fn() => [])
            ->afterCreating(function (Document $document) {
                Storage::disk('s3')->put($document->pdf_path, 'content');
            });
    }

    public function withUploader(): self
    {
        return $this->state(fn($attrs) => ['uploaded_by' => User::factory()]);
    }
}
