<?php

namespace App\Providers;

use App\Creditreform\CreditreformApi;
use App\Models\DocumentType;
use Cache;
use App\Lexoffice\LexofficeApi;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LexofficeApi::class, function () {
            return new LexofficeApi();
        });

        $this->app->bind(CreditreformApi::class, function () {
            return new CreditreformApi();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*Cache::remember('document_types', (60*60), function () {
            return DocumentType::all();
        });*/

        Paginator::useBootstrap();
    }
}
