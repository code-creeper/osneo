<?php

use App\Livewire\DocumentsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (){
    login();
    $this->component = Livewire::test(DocumentsManager::class);
});

it('can render component', function () {
    $this->component->assertSuccessful();
})->todo();

it('can select folder', function () {

})->todo();

it('can prepare inbox files', function () {

})->todo();

it('can restore document', function () {

})->todo();

it('can send document to lexoffice', function () {

})->todo();

it('can delete document', function () {

})->todo();
