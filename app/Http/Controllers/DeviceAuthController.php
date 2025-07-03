<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeviceConfiguration;
use App\Models\Device;
use Illuminate\Support\Facades\Storage;

class DeviceAuthController extends Controller
{
    public function showLogin()
{
    $devices = Device::where('is_active', 1)->get(); // Fetch active only

    return view('device-login', compact('devices'));
}

 public function login(Request $request)
{
    $request->validate([
        'device_id' => 'required|exists:devices,device_id'
    ]);

    $device = \DB::connection('mysql')->table('devices')
                ->where('device_id', $request->device_id)
                ->where('is_active', 1)
                ->first();

    if (!$device) {
        return back()->withErrors(['device_id' => 'Invalid or inactive device.']);
    }

    session([
        'device_id' => $device->device_id,
        'device_info' => $device,
    ]);

    return redirect()->route('front.view');
}


    // Usage elsewhere:
    // $device = session('device_info');
    // $locationId = $device->location_id;
    // $deviceName = $device->name;
}