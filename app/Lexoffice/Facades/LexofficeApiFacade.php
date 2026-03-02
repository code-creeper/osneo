<?php

namespace App\Lexoffice\Facades;

use App\Lexoffice\LexofficeApi;
use Illuminate\Support\Facades\Facade;
use Tests\Fakes\LexofficeApiFake;

class LexofficeApiFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LexofficeApi::class;
    }

    public static function fake()
    {
        return tap(new LexofficeApiFake(), function ($fake) {
            static::swap($fake);
        });
    }
}
