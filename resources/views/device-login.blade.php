<!DOCTYPE html>
<html>
<head>
    <title>Device Login</title>
</head>
<body>
    <h2>Select Your Device</h2>

    @if ($errors->any())
        <div style="color: red;">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('device.login') }}">
        @csrf

        <label for="device_id">Device:</label>
        <select name="device_id" required>
            <option value="">-- Select Device --</option>
            @foreach ($devices as $device)
                <option value="{{ $device->device_id }}">
                    {{ $device->name }} ({{ $device->device_id }})
                </option>
            @endforeach
        </select>

        <br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
