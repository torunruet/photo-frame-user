<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DeviceAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('authenticated_device')) {
            return redirect()->route('device.login');
        }

        return $next($request);
    }
}
