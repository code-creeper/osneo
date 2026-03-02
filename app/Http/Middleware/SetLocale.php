<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;

class SetLocale
{

    public function handle($request, Closure $next): mixed
    {
        $locales = getLocales();

        $locale = session()->get('locale');
        if (session()->has('locale') && \Arr::has($locales, $locale)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
