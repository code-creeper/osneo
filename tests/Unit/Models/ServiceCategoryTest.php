<?php

use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('relations', function (){
    $category = ServiceCategory::factory()->hasServices()->create();

    expect($category->services->count())->toBe(1);
});
