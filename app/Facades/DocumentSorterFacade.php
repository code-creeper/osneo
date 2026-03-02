<?php

namespace App\Facades;

use App\Services\DocumentSorterService;
use Illuminate\Support\Facades\Facade;

class DocumentSorterFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DocumentSorterService::class;
    }
}
