<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogActivity
{
    protected array $except = [
        'logs/activity',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ( ! $this->inExceptArray($request)) {
            activity()
                ->inLog('page_visit')
                ->by(auth()->user())
                ->withProperties(['url' => $request->url()])
                ->log("Visited a Page");
        }

        return $next($request);
    }

    protected function inExceptArray($request): bool
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
