<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingRedirectController extends Controller
{
    public function index()
    {
        if (session()->has('device_id')) {
            return redirect()->route('front.view');
        } else {
            return redirect()->route('device.login.form');
        }
    }
}
