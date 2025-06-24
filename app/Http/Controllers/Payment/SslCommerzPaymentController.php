<?php

namespace App\Http\Controllers\Payment;

use DB;
use Illuminate\Http\Request;
use App\Library\SslCommerz\SslCommerzNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\BillingInfo;
use Carbon\Carbon;

class SslCommerzPaymentController extends Controller
{


public function storePaymentData(Request $request){
    $mapping = $request->input('mapping');
    $total = $request->input('total');
    $mergedImages = $request->input('merged_images', []);

    Session::put('payment.mapping', $mapping);
    Session::put('payment.total', $total);
    Session::put('payment.merged_images', $mergedImages);

    return response()->json(['success' => true]);
}
public function showBillingForm()
{
    // Retrieve mapping, merged images, and total from session
    $mapping = Session::get('payment.mapping', []);
    $mergedImages = Session::get('payment.merged_images', []);
    $total = Session::get('payment.total', 0);

    // Pass all necessary data to the view
    return view('payment.billing', compact('mapping', 'mergedImages', 'total'));
}
public function pay(Request $request)
{
    // If skip billing is selected, use default values
    if ($request->has('skip_billing')) {
        $validated = [
            'cus_name' => 'Guest User',
            'cus_email' => 'guest@example.com',
            'cus_phone' => '0000000000',
            'cus_add1' => 'Not Provided',
        ];
    } else {
        // Validate form data
        $validated = $request->validate([
            'cus_name' => 'required|string|max:255',
            'cus_email' => 'required|email',
            'cus_phone' => 'required|string|max:15',
            'cus_add1' => 'nullable|string|max:255',
        ]);
    }
    
    // Get total amount from session and ensure it's numeric
    $total_amount = Session::get('payment.total');
    if (!is_numeric($total_amount)) {
        return redirect()->back()->withErrors(['error' => 'Invalid total amount']);
    }

    // Process the payment
    $post_data = [
        'total_amount' => (float)$total_amount, // Convert to float to ensure numeric value
        'currency' => "BDT",
        'tran_id' => uniqid(),
        'success_url' => url('/success'),
        'fail_url' => url('/fail'),
        'cancel_url' => url('/cancel'),
        'cus_name' => $validated['cus_name'],
        'cus_email' => $validated['cus_email'],
        'cus_phone' => $validated['cus_phone'],
        'cus_add1' => $validated['cus_add1'],
        'cus_city' => "Dhaka",
        'cus_country' => "Bangladesh",
        'shipping_method' => "NO",
        'product_name' => "Framed Images",
        'product_category' => "image",
        'product_profile' => "general",
        'value_a' => $validated['cus_name'], // Store customer data in JSON format
        'value_b' => $validated['cus_email'],
        'value_c' => $validated['cus_phone'],
        'value_d' => $validated['cus_add1'],
    ];
    Session::put('customerData', $validated); // Store customer data in session for later use
    // Call the SSLCOMMERZ API and redirect to payment gateway
    $sslc = new \App\Library\SslCommerz\SslCommerzNotification();
    $payment_options = $sslc->makePayment($post_data, 'checkout');


    // Decode the response
    $payment_options = json_decode($payment_options, true);
    
    // Check the status and proceed
    if ($payment_options['status'] === 'success') {
        return redirect($payment_options['data']);
    } else {
        return redirect()->route('payment.failure');
    }
}


public function startPayment()
{
    $post_data = array();
    $post_data['total_amount'] = Session::get('total_price'); // Amount
    $post_data['currency'] = "BDT";
    $post_data['tran_id'] = uniqid(); // unique transaction ID

    // Customer details
    $post_data['cus_name'] = "Customer";
    $post_data['cus_email'] = "customer@example.com";
    $post_data['cus_add1'] = "Address";
    $post_data['cus_city'] = "City";
    $post_data['cus_postcode'] = "1000";
    $post_data['cus_country'] = "Bangladesh";
    $post_data['cus_phone'] = "01711111111";

    // Shipping (optional)
    $post_data['ship_name'] = "Store Test";
    $post_data['ship_add1'] = "Dhaka";
    $post_data['ship_city'] = "Dhaka";
    $post_data['ship_postcode'] = "1000";
    $post_data['ship_country'] = "Bangladesh";
    $post_data['shipping_method'] = "NO";
    $post_data['product_name'] = "Computer";
    $post_data['product_category'] = "Goods";
    $post_data['product_profile'] = "physical-goods";
    // Optional Parameters
    $post_data['value_a'] = 'anything extra';
    $post_data['value_b'] = json_encode(Session::get('image_frame_mapping')); // Save mapping for later use

    $sslc = new \App\Library\SslCommerz\SslCommerzNotification();
    $payment_options = $sslc->makePayment($post_data, 'hosted');

    if (!is_array($payment_options)) {
        print_r($payment_options);
    }
}

    public function exampleEasyCheckout()
    {
        return view('exampleEasycheckout');
    }

    public function exampleHostedCheckout()
    {
        return view('exampleHosted');
    }

    public function index(Request $request)
    {
        
        # Here you have to receive all the order data to initate the payment.
        # Let's say, your oder transaction informations are saving in a table called "orders"
        # In "orders" table, order unique identity is "transaction_id". "status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $post_data = array();
        $post_data['total_amount'] = '10'; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = 'Customer Name';
        $post_data['cus_email'] = 'customer@mail.com';
        $post_data['cus_add1'] = 'Customer Address';
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = '8801XXXXXXXXX';
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = "Computer";
        $post_data['product_category'] = "Goods";
        $post_data['product_profile'] = "physical-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";

        #Before  going to initiate the payment order status need to insert or update as Pending.
        $update_product = DB::table('orders')
            ->where('transaction_id', $post_data['tran_id'])
            ->updateOrInsert([
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'amount' => $post_data['total_amount'],
                'status' => 'Pending',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency']
            ]);

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->makePayment($post_data, 'hosted');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }

    public function payViaAjax(Request $request)
    {

        # Here you have to receive all the order data to initate the payment.
        # Lets your oder trnsaction informations are saving in a table called "orders"
        # In orders table order uniq identity is "transaction_id","status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $post_data = array();
        $post_data['total_amount'] = '10'; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = 'Customer Name';
        $post_data['cus_email'] = 'customer@mail.com';
        $post_data['cus_add1'] = 'Customer Address';
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = '8801XXXXXXXXX';
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = "Computer";
        $post_data['product_category'] = "Goods";
        $post_data['product_profile'] = "physical-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";


        #Before  going to initiate the payment order status need to update as Pending.
        $update_product = DB::table('orders')
            ->where('transaction_id', $post_data['tran_id'])
            ->updateOrInsert([
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'amount' => $post_data['total_amount'],
                'status' => 'Pending',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency']
            ]);

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->makePayment($post_data, 'checkout', 'json');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }

 public function success(Request $request)
{
        \Log::info('Full SSLCOMMERZ Response:', $request->all());
    $customerData = session('customerData', []);
    // 1. Get all billing info from payment response
    $name = $customerData['cus_name'] ?? $request->input('value_a');
    $email = $customerData['cus_email'] ?? $request->input('value_b');
    $phone = $customerData['cus_phone'] ?? $request->input('value_c');
    $address = $customerData['cus_add1'] ?? $request->input('value_d');
    $totalAmount = $request->input('amount') ?? 0;

    \Log::info('Saving BillingInfo:', [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
    ]);

    // 2. Save it to Admin Panel's DB
    BillingInfo::create([
        'bill_date'            => Carbon::now()->toDateString(),
        'bill_total_amount'    => $totalAmount,
        'customer_name'        => $name,
        'customer_email'       => $email,
        'customer_phone_no'    => $phone,
        'customer_address'     => $address,
    ]);

    return redirect()->route('thankyou')->with('success', 'Payment successful & billing info saved to admin DB.');
}


    public function fail(Request $request)
    {
        $tran_id = $request->input('tran_id');

        $order_details = DB::table('orders')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status', 'currency', 'amount')->first();

        if ($order_details->status == 'Pending') {
            $update_product = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->update(['status' => 'Failed']);
            echo "Transaction is Falied";
        } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {
            echo "Transaction is already Successful";
        } else {
            echo "Transaction is Invalid";
        }

    }

    public function cancel(Request $request)
    {
        $tran_id = $request->input('tran_id');

        $order_details = DB::table('orders')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status', 'currency', 'amount')->first();

        if ($order_details->status == 'Pending') {
            $update_product = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->update(['status' => 'Canceled']);
            echo "Transaction is Cancel";
        } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {
            echo "Transaction is already Successful";
        } else {
            echo "Transaction is Invalid";
        }


    }

    public function ipn(Request $request)
    {
        #Received all the payement information from the gateway
        if ($request->input('tran_id')) #Check transation id is posted or not.
        {

            $tran_id = $request->input('tran_id');

            #Check order status in order tabel against the transaction id or order id.
            $order_details = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->select('transaction_id', 'status', 'currency', 'amount')->first();

            if ($order_details->status == 'Pending') {
                $sslc = new SslCommerzNotification();
                $validation = $sslc->orderValidate($request->all(), $tran_id, $order_details->amount, $order_details->currency);
                if ($validation == TRUE) {
                    /*
                    That means IPN worked. Here you need to update order status
                    in order table as Processing or Complete.
                    Here you can also sent sms or email for successful transaction to customer
                    */
                    $update_product = DB::table('orders')
                        ->where('transaction_id', $tran_id)
                        ->update(['status' => 'Processing']);

                    echo "Transaction is successfully Completed";
                }
            } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {

                #That means Order status already updated. No need to udate database.

                echo "Transaction is already successfully Completed";
            } else {
                #That means something wrong happened. You can redirect customer to your product page.

                echo "Invalid Transaction";
            }
        } else {
            echo "Invalid Data";
        }
    }

}
