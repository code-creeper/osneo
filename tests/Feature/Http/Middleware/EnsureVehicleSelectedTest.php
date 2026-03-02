<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('todo', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
})->todo();
