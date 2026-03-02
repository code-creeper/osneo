<?php

use App\Models\Contract;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('will create contract in lexoffice when created', function () {
    $contract = Contract::factory()->withoutLexoffice()->create();
    expect($contract)->lexoffice_id->not->toBeNull();
});

it('it has relations', function () {
    $contract = Contract::factory()
        ->forCustomer()
        ->forTicket()
        ->hasDocuments()
        ->create();

    expect($contract)
        ->customer->exists()->toBeTrue()
        ->ticket->exists()->toBeTrue()
        ->documents->count()->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Methods
|--------------------------------------------------------------------------
*/

//getAmount
it('can get amount', function () {
    $contract = Contract::factory()->create();

    $services = [];
    foreach ($contract->services as $service){
        $service['price'] = '10.00';
        $services[] = $service;
    }

    $contract->services = $services;
    $amount = (float)$contract->services->count() * 10;


    expect($contract->getAmount())->toBe($amount);
});

it('can prepare data to send to lexoffice', function (Contract $contract) {
    $data = $contract->prepareContractDataForLexoffice();
    $keys = [
        'voucherDate',
        'expirationDate',

        'address.contactId',
        'address.name',
        'address.supplement',
        'address.street',
        'address.city',
        'address.zip',
        'address.countryCode',

        'lineItems',

        'totalPrice.currency',
        'taxConditions.taxType',

        'paymentConditions.paymentTermLabel',
        'paymentConditions.paymentTermDuration',

        'introduction',
        'remark',
        'title',
    ];

    expect($data)->toHaveKeys($keys);

    $lineItem = $data['lineItems'][0];

    if ($contract->ticket_id){
        expect($data['lineItems'])->toHaveCount(count($contract->services) + 1)
        ->and($lineItem)
            ->type->toBe('text')
            ->name->toBe("Vorgangsnummer: {$contract->ticket->number}");
    } else {
        expect($data['lineItems'])->toHaveCount(count($contract->services))
            ->and($lineItem)
            ->type->toBe('custom')
            ->and($lineItem)->toHaveKeys([
                'type',
                'name',
                'description',
                'quantity',
                'unitName',
                'unitPrice.currency',
                'unitPrice.netAmount',
                'unitPrice.taxRatePercentage',
            ]);
    }

})->with([
    'with_ticket' => fn() => Contract::factory()->create(),
    'without_ticket' => fn() => Contract::factory()->create(['ticket_id' => null]),
]);

//sendToLexOffice
describe('send to lexoffice', function (){
    it('will not send contract to lexoffice if it is already synced', function () {
        $contract = Contract::factory()->create();

        $contract->sendToLexOffice();

        expect($contract->wasChanged())->toBeFalse();
    });

    it('will update lexoffice payload after syncing the contract with lexoffice', function (){
        $response = json_decode(fixture('Lexoffice/get_quotation.json'));
        $contract = Contract::factory()->create(['lexoffice_id' => null]);

        $contract->sendToLexOffice();

        expect($contract)->lexoffice_id->toBe($response->id)
        ->lexoffice_payload->toArray()->not->toBeEmpty();

    });
});

//getDocumentName
it('can get document name', function () {
    $contract = Contract::factory()->create();

    expect($contract->getDocumentName())
        ->not->toBeNull()
        ->and($contract->getDocumentName())->toEndWith('.pdf');
});

//getQuotation
describe('getQuotation', function (){
    it('will return null if contract is not synced', function (){
        $contract = Contract::factory()->withoutLexoffice()->createQuietly();
        expect($contract->getQuotation())->toBeNull();
    });

    it('will get quotation from database if present', function () {
        $contract = Contract::factory()->withoutLexoffice()->create();
        LexofficeApi::shouldReceive('get_quotation')->never();

        expect($contract->getQuotation())->id->not->toBeNull();
    });

    it('will get quotation from api if not present in database', function () {
        $contract = Contract::factory()->notSynced()->create();
        expect($contract->getQuotation())->id->not->toBeNull();
    });
});

//downloadDocumentToInbox
it('can download contract document to inbox', function () {
    $contract = Contract::factory()->notSynced()->create();
    $documentName = "$contract->lexoffice_id.pdf";
    $contract->downloadDocumentToInbox($documentName);

    Storage::assertExists(Document::$inboxDir."/$documentName");
});

//getDocument
describe('getDocument', function (){
    it('will return existing document if document already exists', function () {
        $contract = Contract::factory()->create();
        $documentName = $contract->getDocumentName();
        $document = Document::factory()->for($contract, 'documentable')->create(['name' => $documentName]);

        expect($contract->getDocument())->id->toBe($document->id);
    });

    it('will create and sort new document if document doest not exist', function (){
        seedDocumentTypes(true);
        $contract = Contract::factory()->create();
        $document = $contract->getDocument();

        expect($document->wasRecentlyCreated)->toBeTrue()
            ->and($document)->sorted->toBeTrue();
    });
});
