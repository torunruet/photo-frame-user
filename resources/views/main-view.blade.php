<!DOCTYPE html>
<html>

<head>
    <title>FrameX - Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
</head>

<body class="bg-gray-50 flex flex-col items-center justify-center min-h-screen p-6">
    <h1 class="text-3xl font-bold mb-6">FrameX Kiosk</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-10">
        <!-- Upload from Mobile -->
        <a href="{{ route('start.session') }}"
            class="bg-white shadow-xl rounded-2xl p-8 text-center hover:bg-blue-100 transition duration-300">
            <div class="text-5xl mb-4">📱</div>
            <h2 class="text-xl font-semibold mb-2">Upload from Mobile</h2>
            <p class="text-gray-600">Scan QR code and upload photos from your phone.</p>
        </a>

        <!-- Take a Selfie -->
        <a href="{{ route('take.photo') }}"
            class="bg-white shadow-xl rounded-2xl p-8 text-center hover:bg-green-100 transition duration-300">
            <div class="text-5xl mb-4">🤳</div>
            <h2 class="text-xl font-semibold mb-2">Take a Selfie</h2>
            <p class="text-gray-600">Use the webcam to capture a photo.</p>
        </a>

    </div>

    <h1 class="text-2xl font-bold mb-4">Let's create some amazing moments</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach ($images as $image)
            <div class="border border-gray-300 rounded-lg p-2">
                <img src="{{ asset('storage/' . $image) }}" alt="Uploaded Image" class="w-full h-auto">
            </div>
        @endforeach
    </div>

    <img id="photoPreview" src="" style="display: none;"
        class="mt-4 w-80 h-auto border border-gray-300 rounded-lg" />

    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env('PUSHER_APP_KEY') }}',
            wsHost: window.location.hostname,
            wsPort: 6001,
            forceTLS: false,
            disableStats: true,
        });

        Echo.channel('homepage-refresh')
            .listen('RefreshHomepage', () => {
                // Reload the page or fetch the updated images
                location.reload();
            });

        Echo.channel("session.{{ $sessionId ?? 'default' }}")
            .listen('PhotoUploaded', (e) => {
                console.log('PhotoUploaded event received:', e);
                const imageElement = document.createElement('div');
                imageElement.classList.add('bg-white', 'shadow', 'rounded-lg', 'p-4');
                imageElement.innerHTML = `<img src="${e.path}" alt="Uploaded Image" class="w-full h-auto rounded-lg">`;
                document.getElementById('uploadedImages').appendChild(imageElement);
            });
    </script>
</body>

</html>
