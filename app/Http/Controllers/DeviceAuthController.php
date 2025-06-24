<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class DeviceAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('device.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
        ]);

        try {
            $response = Http::post('http://127.0.0.1:8081/api/device/authenticate', [
                'device_id' => $request->device_id
            ]);

            if ($response->successful()) {
                // Store device info in session
                Session::put('authenticated_device', $response->json()['device']);
                return redirect()->route('front.view');
            }

            return back()->withErrors([
                'device_id' => 'Invalid device ID. Please try again.'
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'device_id' => 'Unable to connect to authentication service. Please try again later.'
            ]);
        }
    }

    public function logout()
    {
        Session::forget('authenticated_device');
        return redirect()->route('device.login');
    }
}
