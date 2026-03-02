<?php

use App\Livewire\Forms\DocumentTypeForm;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(DocumentTypeForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

