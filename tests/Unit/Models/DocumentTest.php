<?php

use App\Enums\DocumentSource;
use App\Models\Document;
use App\Models\DocumentProperty;
use App\Models\DocumentType;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

describe('boot', function () {
    it('will sync users using subscriber_ids on update', function () {
        seedDocumentTypes();
        $user = User::factory()->create();
        $documentType = DocumentType::first();
        $documentType->update([
            'subscriber_ids' => [$user->id],
        ]);

        $document = Document::factory()->create();

        expect($document->users)->toHaveCount(0);

        $document->update([
            'document_type_id' => $documentType->id
        ]);

        expect($document->users()->count())->toBe(1);

    });

    it('will make document name unique on restore', function (){
        $name = 'invoice.pdf';
        $document = Document::factory()->create(['name' => $name]);

        expect($document)->name->toBe($name);

        // Delete and create a new document with the same name
        $document->delete();
        $document2 = Document::factory()->create(['name' => $name]);
        expect($document2)->name->toBe($name);

        // Restore the original document and ensure a unique name is generated
        $document->restore();
        expect($document)->name->toBe('invoice(1).pdf');
    });

    it('will make document sorted_path unique on restore', function (){
        $sortedPath = 'some/sorted/dir/document.pdf';
        $document = Document::factory()->sorted()->create(['sorted_path' => $sortedPath]);

        expect($document)->sorted_path->toBe($sortedPath);

        // Delete and create a new document with the same sorted_path
        $document->delete();
        $document2 = Document::factory()->sorted()->create(['sorted_path' => $sortedPath]);
        expect($document2)->sorted_path->toBe($sortedPath);

        // Restore the original document and ensure a unique path is generated
        $document->restore();
        expect($document)->sorted_path->toBe('some/sorted/dir/document(1).pdf');
    });

    it('will move document file from trash on restore', function (){
        $document = Document::factory()->trashed()->withFile()->create();

        // assert that file will not be present in trash folder after restore
        $document->restore();
        Storage::assertMissing($document->trashPath())
            ->assertExists($document->inboxPath());
    })->todo('Has some weird error');

    it('will move document file to trash on soft delete', function (){
        $document = Document::factory()->withFile()->create();
        $document->delete();

        Storage::assertExists($document->trashPath())
            ->assertMissing($document->inboxPath());
    })->todo('Has some weird error');

    it('will delete document file on permanent delete', function (){
        $document = Document::factory()->withFile()->create();

        Storage::assertExists($document->inboxPath());

        $document->forceDelete();
        Storage::assertMissing($document->pdf_path);
    });
});

test('relations', function () {
    seedDocumentTypes();

    $document = Document::factory()
        ->hasUsers()
        ->forSorter()
        ->forUploader()
        ->has(Invoice::factory())
        ->create([
            'document_type_id' => 1,
        ]);


    expect($document)
        ->documentType->exists()->toBeTrue()
        ->invoice->exists()->toBeTrue()
        ->uploader->exists()->toBeTrue()
        ->sorter->exists()->toBeTrue()
        ->users->count()->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Scopes
|--------------------------------------------------------------------------
*/

//scopeRelevant
it('will get relevant documents', function () {
    Document::truncate();

    Document::factory(3)->create();
    expect(Document::count())->toBe(3);

    seed(PermissionSeeder::class);

    $user = User::factory()->hasAttached(Document::factory(3))->create();

    loginWithPermissions('view own documents', $user);
    Document::factory(2)->for(auth()->user(), 'uploader')->create();

    expect(Document::relevant()->count())->toBe(2);

    auth()->user()->givePermissionTo('view assigned documents');
    expect(Document::relevant()->count())->toBe(5);

    auth()->user()->givePermissionTo('view all documents');
    expect(Document::relevant()->count())->toBe(8);

});

//scopeInbox
it('will get inbox files', function () {
    Document::truncate();

    $fileNames = ['#NICHT LÖSCHEN!!!.md', 'document1.pdf', 'document2.pdf', 'document3.pdf'];

    foreach ($fileNames as $name){
        Document::factory()->create(['name' => $name]);
    }

    Document::factory(3)->create();
    Document::factory(3)->sorted()->create();

    expect(Document::count())->toBe(10)
        ->and(Document::inbox($fileNames)->count())->toBe(3);
});

//scopeSorted
it('will get sorted documents', function () {
    Document::truncate();

    Document::factory(3)->create();
    Document::factory(3)->sorted()->create();

    expect(Document::query()->sorted()->count())->toBe(3)
        ->and(Document::count())->toBe(6);
});

//scopeAllowed
it('will get allowed documents', function () {
    Document::truncate();
    seedDocumentTypes();

    Document::factory()->count(10)->sequence(
        ['document_type_id' => 1],
        ['document_type_id' => 2],
    )->create();

    expect(Document::allowed(1)->count())->toBe(5)
        ->and(Document::allowed([1,2])->count())->toBe(10)
        ->and(Document::count())->toBe(10);

    loginWithPermissions('view all documents');
    expect(Document::allowed(1)->count())->toBe(10);
});

/*
|--------------------------------------------------------------------------
| Attributes
|--------------------------------------------------------------------------
*/

//getPdfPathAttribute
describe('pdf path attribute', function (){
    it('will return inbox path if document is not sorted or deleted', function () {
        $document = Document::factory()->create();
        expect($document)->pdf_path->toBe($document->inboxPath());
    });

    it('will return trash path if document is trashed', function () {
        $document = Document::factory()->trashed()->create();
        expect($document)->pdf_path->toBe($document->trashPath());
    });

    it('will return sorted path if document is sorted', function () {
        $document = Document::factory()->sorted()->create();
        expect($document)->pdf_path->toBe($document->sorted_path);
    });
});

//sorted
it('has sorted attribute', function () {
    $document = Document::factory()->create([
        'status' => 2,
        'sorted_on' => now()
    ]);

    expect($document->sorted)->toBeFalse();

    $document->update([
        'sorted_path' => 'some/sorted/dir'
    ]);

    expect($document->sorted)->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Methods
|--------------------------------------------------------------------------
*/

//name
it('can get document name', function () {
    $document = Document::factory()->create(['name' => 'invoice.pdf']);
    expect($document->name())->toBe('invoice.pdf');

    $sortedPath = 'Ablage/KRE/LFS/2020/07/23072020_KRE_0815_GC_48303768-001_T-5760.pdf';

    $document->update([
        'sorted' => 2,
        'sorted_path' => $sortedPath
    ]);

    expect($document->name())->toBe('23072020_KRE_0815_GC_48303768-001_T-5760.pdf');
});

//isLexOfficeFile
it('can check if document is lexoffice file', function ($lexofficeId, $result) {
    $document = Document::factory()->create([
        'lexoffice_id' => $lexofficeId
    ]);

    expect($document)->isLexOfficeFile()->toBe($result);
})->with([
    [null, false],
    [str()->uuid(), true],
]);

//inboxPath
it('can get inbox path', function () {
    $document = Document::factory()->create(['name' => 'invoice.pdf']);
    expect($document->inboxPath())->toBe(Document::$inboxDir.'/invoice.pdf');
});

//trashPath
it('can get trash path', function () {
    $document = Document::factory()->create(['name' => 'invoice.pdf']);
    expect($document->trashPath())->toBe(Document::$trashDir.'/invoice.pdf');
});

//makeNameUnique
it('will make unique document name', function () {
    $document = new Document();
    $name = 'invoice.pdf';

    // the name should be same when there is no duplicate
    expect($document)->makeNameUnique($name)->toBe($name);

    Document::factory()->create(['name' => $name]);
    $document->makeNameUnique($name);

    expect($document)->makeNameUnique($name)->toBe('invoice(1).pdf');

    $sortedPath = 'some/sorted/dir/document.pdf';

    $sortedDocument = Document::factory()->sorted()->create(['sorted_path' => $sortedPath,]);
    expect($sortedDocument)->makeNameUnique($sortedPath, 'sort')->toBe('some/sorted/dir/document(1).pdf');

    // we do not persist the document to database! so we can test the $sortedPath on some other date
    // the name should not be unique, if the duplicate document is not sorted on same date
    $sortedDocument2 = Document::factory()->sorted()->make([
        'sorted_path' => $sortedPath,
        'sorted_on' => now()->subDays()
    ]);

    expect($sortedDocument2)->makeNameUnique($sortedPath, 'sort')->toBe($sortedPath);
});

//getUrl
describe('get document url', function (){
    beforeEach(fn() => Storage::fake('s3'));

    it('should have null url when file does not exist', function () {
        $document = Document::factory()->create();
        expect($document->getUrl())->toBeNull();
    });

    it('should have null url for document with invalid extension', function () {
        $document = Document::factory()->withFile()->create([
            'name' => 'document.no_ext'
        ]);

        // file exists, but invalid extension
        expect(Storage::exists($document->pdf_path))
            ->and($document->getUrl())->toBeNull();
    });

    it('should not have null url for sorted document', function () {
        $document = Document::factory()->sorted()->withFile()->create();
        expect($document->getUrl())->not->toBeNull();
    });
});

//getInboxFiles
it('can get names of files in inbox directory', function () {
    foreach (range(1, 3) as $fileNumber){
        Storage::disk('s3')->put(Document::$inboxDir. "/invoice($fileNumber).pdf", 'content');
    }

    $files = Document::getInboxFiles();

    expect(count($files))->toBe(3)
    ->and($files[0])->not->toStartWith(Document::$inboxDir);

    $files = Document::getInboxFiles(true);
    expect($files[0])->toStartWith(Document::$inboxDir);
});

//generateName
it('can generate document name', function () {
    DocumentProperty::forceTruncate();

    $document = Document::factory()
        ->forDocumentType(['key' => 'R'])
        ->create([
            'sorted_on' => '2022-02-01',
            'source' => DocumentSource::DEB->name,
            'name' => 'document.pdf',
        ]);

    DocumentProperty::factory()->for($document->documentType)->create(['is_name' => 0]);
    DocumentProperty::factory()->for($document->documentType)->isName()->create(['order' => 2, 'name' => 'test_property']);
    DocumentProperty::factory()->for($document->documentType)->isName()->create(['order' => 3, 'name' => 'test_property2']);
    DocumentProperty::factory()->for($document->documentType)->isName()->create(['order' => 1, 'name' => 'characters_test']);

    $document->generateName();

    // without properties
    $sortedPath = Document::$sortedDir. "/DEB/R/2022/02/01022022_DEB_R.pdf";
    expect($document->sorted_path)->toBe($sortedPath);

    $document->properties = [
        2 => ['value' => 'test_property_value'],
        3 => ['value' => 'test_property2_value'],
        4 => ['value' => 'characters_test/\\:*?"<>|'],
    ];
    $document->save();

    $properties = DocumentProperty::forNaming($document->document_type_id)->get();

    $filename = str('');
    foreach ($properties as $property){
        $propertyValue = $document->properties->get($property->id)['value'] ?? '';
        $filename = $filename->append("_$propertyValue");
    }

    $document->generateName();

    // properties should be generated in order
    $sortedPath = Document::$sortedDir. "/DEB/R/2022/02/01022022_DEB_R_test_property2_value_test_property_value_characters_test.pdf";
    expect($document->sorted_path)->toBe($sortedPath);

    //cleanup
    DocumentProperty::forceTruncate();
    seedDocumentProperties();
});

//createInvoice
it('can create invoice', function () {
    Invoice::truncate();
    seedDocumentTypes();

    $invoiceDocument = Document::factory()->create(['document_type_id' => 1]);
    $otherDocument = Document::factory()->create(['document_type_id' => 2]);

    $invoiceDocument->createInvoice();
    expect($invoiceDocument->invoice?->id)->toBe(1);

    $otherDocument->createInvoice();
    expect($otherDocument->invoice?->id)->toBeNull();
});



