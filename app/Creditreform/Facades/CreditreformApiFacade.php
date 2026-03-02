<?php

namespace App\Creditreform\Facades;

use App\Creditreform\CreditreformApi;
use Illuminate\Support\Facades\Facade;
use Tests\Fakes\CreditreformApiFake;

class CreditreformApiFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CreditreformApi::class;
    }

    public static function fake()
    {
        return tap(new CreditreformApiFake(), function ($fake) {
            static::swap($fake);
        });
    }
}
