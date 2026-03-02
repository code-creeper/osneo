<?php

use function Pest\Laravel\get;

it('renders home page', function () {
    $response = get('/');
    $response->assertRedirect('/login');
});

