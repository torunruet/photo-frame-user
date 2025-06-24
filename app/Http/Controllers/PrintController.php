<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\BillingInfo;

class PrintController extends Controller
{
    public function show()
    {
        $printData = Session::get('print_data');

        if (!$printData) {
            return redirect('/')->with('error', 'No print data available');
        }

        return view('print-page', [
            'imageFrameData' => $printData['imageFrameData'] ?? [],
            'amount' => $printData['amount'] ?? 0
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'customer_name'      => 'required|string',
            'customer_email'     => 'required|email',
            'customer_phone_no'  => 'required|string',
            'customer_address'   => 'required|string',
            'bill_total_amount'  => 'required|numeric',
        ]);

        BillingInfo::create([
            'bill_date'          => now(), // or you can omit this if it's set by DB
            'bill_total_amount'  => $request->input('bill_total_amount'),
            'customer_name'      => $request->input('customer_name'),
            'customer_email'     => $request->input('customer_email'),
            'customer_phone_no'  => $request->input('customer_phone_no'),
            'customer_address'   => $request->input('customer_address'),
        ]);

        return redirect()->back()->with('success', 'Printed and Billing info saved!');
    }
}

