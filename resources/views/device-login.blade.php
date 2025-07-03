<!DOCTYPE html>
<html>
<head>
    <title>Device Login</title>

    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        select {
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <h2>Select Your Device</h2>

    @if ($errors->any())
        <div style="color: red;">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('device.login') }}">
        @csrf

        <label for="device_id">Device:</label>
        <select name="device_id" class="device-select" required>
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

    <script>
        $(document).ready(function() {
            $('.device-select').select2({
                placeholder: "-- Select Device --",
                allowClear: true,
                tags: true, // allows manual typing/pasting
                width: 'resolve',
                matcher: function(params, data) {
                    // Custom matcher for searching device ID or name
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    if (typeof data.text === 'undefined') {
                        return null;
                    }

                    const term = params.term.toLowerCase();
                    const text = data.text.toLowerCase();
                    const value = (data.element && data.element.value) ? data.element.value.toLowerCase() : '';

                    if (text.includes(term) || value.includes(term)) {
                        return data;
                    }

                    return null;
                }
            });
        });
    </script>
</body>
</html>
