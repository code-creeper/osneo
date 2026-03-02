<?php

namespace App\Creditreform\Facades;

use App\Creditreform\CreditreformService;
use Illuminate\Support\Facades\Facade;

class CreditreformServiceFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CreditreformService::class;
    }
}
