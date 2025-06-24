<!DOCTYPE html>
<html>

<head>
    <title>Billing Info</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-4 mb-4 max-w-lg mx-auto mt-6 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $mapping = session('payment.mapping', []);
        $total = session('payment.total', 0);
        $mergedImages = session('payment.merged_images', []);
    @endphp

    <div class="flex-1 flex flex-col items-center justify-start w-full pt-10">
        <!-- Image Frames Grid -->
        <div class="w-full max-w-4xl grid grid-cols-1 sm:grid-cols-2 gap-8 mb-8">
            @foreach ($mapping as $index => $item)
                <div class="bg-white p-4 rounded shadow-md flex flex-col items-center">
                    <img src="{{ $mergedImages[$index] ?? '' }}" class="w-[22rem] h-64 object-contain rounded mb-2"
                        alt="Merged Image">
                    <div class="text-sm text-gray-700">
                        Frame ID: <strong>{{ $item['frame_id'] }}</strong><br>
                        Quantity: <strong>{{ $item['quantity'] }}</strong>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Total Amount -->
        <div class="mb-10 w-full flex justify-center">
            <h2 class="text-2xl font-bold text-center bg-white px-8 py-4 rounded shadow">Total Amount:
                {{ $total }} tk</h2>
        </div>

        <!-- Billing Form -->
        <div class="w-full flex justify-center pb-12">
            <form action="{{ route('sslcommerz.checkout') }}" method="POST"
                class="bg-white p-8 rounded shadow-md w-full max-w-md">
                @csrf

                <!-- Hidden Fields to send total and mapping -->
                <input type="hidden" name="total" value="{{ $total }}">
                <input type="hidden" name="mapping" value='@json($mapping)'>

                <h2 class="text-xl font-bold mb-4 text-center">Billing Information</h2>

                <div class="mb-4">
                    <label class="block mb-1">Name</label>
                    <input type="text" name="cus_name" class="w-full border rounded p-2" required>
                </div>

                <div class="mb-4">
                    <label class="block mb-1">Email</label>
                    <input type="email" name="cus_email" class="w-full border rounded p-2" required>
                </div>

                <div class="mb-4">
                    <label class="block mb-1">Phone</label>
                    <input type="text" name="cus_phone" class="w-full border rounded p-2" required>
                </div>

                <div class="mb-4">
                    <label class="block mb-1">Address</label>
                    <input type="text" name="cus_add1" class="w-full border rounded p-2">
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded flex-1">Checkout / Pay</button>
                    <button type="submit" name="skip_billing" value="1"
                        class="bg-gray-500 text-white px-4 py-2 rounded flex-1">Skip Billing</button>
                </div>

            </form>
        </div>
    </div>

    <!-- Step 3: JS to send data to Admin DB before form submit -->
    <script>
function sendBillingToAdmin(billingData) {
    fetch('http://127.0.0.1:8081/api/billing/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(billingData)
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Billing sent to admin:', data);
    })
    .catch(error => {
        console.error('❌ Error sending billing info:', error);
    });
}
</script>


</body>

</html>
