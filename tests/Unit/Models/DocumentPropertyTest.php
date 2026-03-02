<?php

use App\Models\DocumentProperty;
use App\Models\DocumentType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('relations', function () {
    $property = DocumentProperty::factory()->forDocumentType()->create();
    expect($property)->documentType->exists()->toBeTrue();
});

//scopeForNaming
it('will get document properties for naming', function () {
    DB::disableForeignKeyChecks();
    DocumentType::truncate();
    DocumentProperty::truncate();
    DB::enableForeignKeyChecks();

    $documentType = DocumentType::factory()->create();

    DocumentProperty::factory()->for($documentType)->create(['active' => 1, 'is_name' => 1]);
    DocumentProperty::factory()->for($documentType)->create(['active' => 0, 'is_name' => 1]);
    DocumentProperty::factory()->for($documentType)->create(['is_name' => 0, 'active' => 1]);
    DocumentProperty::factory()->for($documentType)->create(['is_name' => 1, 'active' => 1]);

    expect(DocumentProperty::forNaming($documentType->id)->count())->toBe(2);
})->todo();
