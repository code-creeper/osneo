<?php

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('relations', function (){
    $service = Service::factory()->forCategory()->create();

    expect($service->category->exists())->toBeTrue();
});
