<?php


use Spatie\Permission\Models\Role;

function user($guard = null): \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable|null
{
    return auth($guard)->user();
}

function getLocales(): array
{
    return [
        'en' => 'English',
        'de' => 'German',
    ];
}
