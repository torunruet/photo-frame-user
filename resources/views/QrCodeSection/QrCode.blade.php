<!DOCTYPE html>
<html>

<head>
    <title>FrameX - Upload from Mobile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
</head>

<body class="bg-gray-50 flex flex-col items-center justify-center h-screen p-6">

    <h1 class="text-2xl font-bold mb-4">Upload Photo from Mobile</h1>

    <canvas id="qrcode" class="mb-6"></canvas>
    <p class="text-gray-600 mb-4">Scan this QR code with your phone to open the upload page.</p>

    <h2 class="mb-4  break-all text-center">Browse URL in your device: <span id="uploadUrlText"
            class="text-blue-600"></span></h2>
    <!-- Back to Home Button -->
    <a href="{{ route('front.view') }}"
        class="mb-6 self-center px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-semibold flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="w-5 h-5 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
        </svg>
        Back to Home
    </a>
    <img id="photoPreview" class="hidden w-80 h-auto border border-gray-300 rounded-lg mb-4" />
    <button id="continueBtn" class="hidden px-4 py-2 bg-green-500 text-white rounded-lg">Continue</button>


    <script>
        const uploadUrl = `http://192.168.0.199:8000{{ route('upload.page', ['session' => $sessionId], absolute: false) }}`;
        console.log('Generated upload URL:', uploadUrl);
        QRCode.toCanvas(document.getElementById('qrcode'), uploadUrl, error => {
            if (error) console.error(error);
        });
        document.getElementById('uploadUrlText').textContent = uploadUrl;

        const echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env('PUSHER_APP_KEY') }}',
            wsHost: window.location.hostname,
            wsPort: 6001,
            forceTLS: false,
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
        });

        echo.channel('homepage-refresh')
            .listen('RefreshHomepage', (data) => {
                console.log('RefreshHomepage event received:', data);
                if (data.session) {
                    // Redirect to the renderingImage page for the session
                    window.location.href = `/rendering-image/${data.session}`;
                }
            });
    </script>
</body>

</html>
