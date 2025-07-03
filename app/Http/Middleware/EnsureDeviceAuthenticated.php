<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureDeviceAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('device_id')) {
            return redirect()->route('device.login.form');
        }

        return $next($request);
    }
}
