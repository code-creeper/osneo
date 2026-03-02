<?php

namespace App\Lexoffice\Facades;

use App\Lexoffice\LexofficeService;
use Illuminate\Support\Facades\Facade;

class LexofficeServiceFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LexofficeService::class;
    }
}
