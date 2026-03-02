<?php

namespace Database\Factories;

use App\Enums\DocumentPropertyType;
use App\Models\DocumentProperty;
use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DocumentPropertyFactory extends Factory
{
    protected $model = DocumentProperty::class;

    public function definition(): array
    {
        $propertyNames = [
            "Debitoren-/Kreditorennummer",
            "Kunden-/Lieferantenname",
            "Rechnungsnummer",
            "Vorgangsnummer",
            "Rechnungssumme (Brutto)",
            "Umsatzsteuer",
            "Fälligkeit",
            "Lieferscheinnummer",
            "Auftragsbestätigungsnummer",
            "Kalkulierte Stundenzahl",
            "Kalkulierter Materialeinsatz",
            "Angebotsnummer",
            "Berichtnummer",
            "Arbeitszeit",
            "Absender",
            "Grund: Freitexteingabe",
            "Freitexteingabe",
            "Zugehörige Rechnungsnummer",
            "Verlust aus Gutschrift",
            "Debitorennummer",
            "Kundenname",
            "WV-Nummer",
            "Interval in Monaten",
            "Kündigungsfrist in Monaten",
            "Abschlussdatum",
            "Mahnungsnummer",
            "Avisnummer",
            "Mitarbeiternamen",
            "Meldung von",
            "Meldung bis",
            "Datum"
        ];

        $name = $this->faker->unique()->randomElement($propertyNames);
        return [
            'key' => fn($attrs) => Str::snake($attrs['name']),
            'name' => $name,
            'description' => null,
            'type' => $this->faker->randomElement(DocumentPropertyType::cases()),
            'rules' => $this->faker->randomElement([null, 'required']),
            'order' => $this->faker->randomDigit(),
            'is_name' => $this->faker->boolean(),
            'active' => 1,

            'document_type_id' => DocumentType::factory(),
        ];
    }

    public function required(): static
    {
        return $this->state(fn($attrs) => ['rules' => 'required']);
    }

    public function isName(): static
    {
        return $this->state(fn($attrs) => ['is_name' => 1]);
    }

    public function text(): static
    {
        return $this->state(fn($attrs) => ['type' => 'Text']);
    }
}
