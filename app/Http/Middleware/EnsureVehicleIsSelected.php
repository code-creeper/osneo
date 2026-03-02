<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureVehicleIsSelected
{
    public array $except = [
        '/',
        '/documents',
        '/attendances',
    ];

    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->attendanceHasStarted()
            && ! $request->user()->hasSelectedVehicle()
			&& user()->can('view vehicles')
        ) {
            session()->put('vehicle_not_selected', true);

            if ( ! $this->inExceptArray($request)) {
                return redirect()->route('dashboard');
            }
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
