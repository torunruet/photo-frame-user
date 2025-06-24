<h2 class="text-lg font-bold">Payment Successful!</h2>
<p>Total Paid: {{ $amount }} TK</p>

@foreach ($imageFrameData as $item)
    <div class="my-4">
        <p>Image: {{ $item['image'] }}</p>
        <p>Frame ID: {{ $item['frame_id'] }}</p>
        <img src="{{ asset('storage/' . $item['image']) }}" style="width: 200px">
    </div>
@endforeach

<hr>

<h3>Fill in your details to save Billing Information</h3>

<form action="{{ route('print.store') }}" method="POST">
    @csrf
    <input name="customer_name" placeholder="Full Name" required>
    <input name="customer_email" type="email" placeholder="Email" required>
    <input name="customer_phone_no" placeholder="Phone Number" required>
    <input name="customer_address" placeholder="Address" required>
    <input name="bill_total_amount" type="hidden" value="{{ $amount }}">  

    <br>
    <button type="submit">Save Billing</button>
</form>

<script>
    // Uncomment if you want to print immediately
    // window.onload = () => window.print();
</script>
