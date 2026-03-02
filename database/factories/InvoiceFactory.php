<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'lexoffice_id' => $this->faker->uuid(),
            'lexoffice_payload' => $this->getLexofficePayload(),
            'creditreform_id' => $this->faker->uuid(),
            'creditreform_payload' => $this->getCreditreformPayload(),
            'type' => $this->faker->randomElement(['voucher']),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'document_id' => Document::factory(),
        ];
    }

    public function paid(): static
    {
        return $this->state(function ($attrs) {
            $payload = $attrs['lexoffice_payload'];
            $payload['voucher']['voucherStatus'] = 'paid';

            return [
                'lexoffice_payload' => $payload,
            ];
        });
    }

    public function open(): static
    {
        return $this->state(function ($attrs) {
            $payload = $attrs['lexoffice_payload'];
            $payload['voucher']['voucherStatus'] = 'open';

            return [
                'lexoffice_payload' => $payload,
            ];
        });
    }

    public function getLexofficePayload(): array
    {
        return [
            "contact" => [
                "id" => "6a395438-6a46-4732-9855-735d79c14c04",
                "roles" => [
                    "customer" => [
                        "number" => 12356
                    ]
                ],
                "company" => [
                    "name" => "Detlef & Ulrike Eckenberg"
                ],
                "version" => 2,
                "archived" => false,
                "addresses" => [
                    "billing" => [
                        [
                            "zip" => "80638",
                            "city" => "München",
                            "street" => "Habermannstraße 3",
                            "countryCode" => "DE"
                        ]
                    ]
                ],
                "organizationId" => "fe51075d-5cf8-4ea7-bb9a-b3f4d40794ff"
            ],
            "payment" => [
                "currency" => "EUR",
                "paidDate" => "2021-11-10T01:00:00.000+01:00",
                "openAmount" => 0,
                "voucherType" => "salesinvoice",
                "paymentStatus" => "balanced",
                "voucherStatus" => "paid"
            ],
            "voucher" => [
                "id" => "238a2ca1-16b8-4984-8381-8ecef3255719",
                "type" => "salesinvoice",
                "files" => [
                    "1de741fd-1968-4dd6-b335-c34dc7b6c11d"
                ],
                "remark" => "T-17934",
                "dueDate" => "2021-11-03T00:00:00.000+01:00",
                "taxType" => "gross",
                "version" => 4,
                "contactId" => "6a395438-6a46-4732-9855-735d79c14c04",
                "createdDate" => "2021-11-03T18:38:04.823+01:00",
                "updatedDate" => "2021-11-18T20:04:57.231+01:00",
                "voucherDate" => "2021-11-03T00:00:00.000+01:00",
                "voucherItems" => [
                    [
                        "amount" => 293.25,
                        "taxAmount" => 46.82,
                        "categoryId" => "8f8664a1-fd86-11e1-a21f-0800200c9a66",
                        "taxRatePercent" => 19
                    ]
                ],
                "voucherNumber" => "R2021/1643",
                "voucherStatus" => $this->faker->randomElement(['open', 'paid']),
                "organizationId" => "fe51075d-5cf8-4ea7-bb9a-b3f4d40794ff",
                "totalTaxAmount" => 46.82,
                "totalGrossAmount" => 293.25,
                "useCollectiveContact" => false
            ]
        ];
    }

    public function getCreditreformPayload(): array
    {
        return [
            "name" => "Phidias Hausverwaltungen GmbH",
            "phone" => null,
            "gender" => "Company",
            "notice" => null,
            "address" => [
                "city" => "München",
                "annex" => null,
                "street" => "Dachauerstraße 431",
                "country" => "DE",
                "postalCode" => "80992"
            ],
            "dueDate" => "2023-10-31",
            "orderNo" => "TKT-230906-092804",
            "bookings" => [
                [
                    "id" => 1721156,
                    "date" => "2023-10-21",
                    "type" => "Invoice",
                    "amount" => 3344.87,
                    "remarks" => null,
                    "cancellationAllowed" => true,
                    "impactInvoiceAmount" => 3344.87
                ],
                [
                    "id" => 1722637,
                    "date" => "2023-10-25",
                    "type" => "Payment",
                    "amount" => 3344.87,
                    "remarks" => "Quelle: Benutzer-Eingabe\r\nIBAN: \r\nKontoinhaber: \r\nVerw.zweck: \r\nGVC: \r\nProtokoll: 25.10.2023 20:13:48 25.10.2023 20:13:48 Zahlung zugeordnet",
                    "cancellationAllowed" => true,
                    "impactInvoiceAmount" => -3344.87
                ]
            ],
            "clientId" => "08db6598-65f4-49ff-8ebe-d2f339e397f7",
            "debtorId" => 402724,
            "debtorNo" => null,
            "nextStep" => null,
            "protocol" => "25.10.2023 20:13:48: Rechnung wird wegen Zahlung 3.344,87 vom 25.10.2023 als bezahlt markiert\r\n25.10.2023 20:13:48: Zahlung in Höhe von 3.344,87 mit Datum 25.10.2023 gebucht",
            "firstName" => null,
            "invoiceId" => "08dbd4ac-e363-4e14-84c0-5cdd40157349",
            "invoiceNo" => "R2023/1718",
            "netAmount" => 2810.82,
            "orderDate" => null,
            "reminders" => [],
            "documentId" => 1072773,
            "externalId" => null,
            "grossAmount" => 3344.87,
            "invoiceDate" => "2023-10-21",
            "creationTime" => "2023-10-24 18:18:41",
            "creationType" => "ApiCall",
            "currentState" => "Paid",
            "customFields" => null,
            "emailAddress" => null,
            "nextStepDate" => null,
            "contactPerson" => null,
            "thumbnailImage" => null,
            "collectionRefNo" => null,
            "emailDocumentId" => null,
            "isUpdateAllowed" => true,
            "openGrossAmount" => 0,
            "isWriteOffAllowed" => false,
            "discountConditions" => [],
            "isNewPaymentAllowed" => true,
            "attachmentDocumentId" => null,
            "dunningStopUntilDate" => null,
            "totalOpenGrossAmount" => 0,
            "isCancellationAllowed" => true,
            "isNewDunningStopAllowed" => false,
            "emailAddressOnly4Dunnings" => null
        ];
    }
}
