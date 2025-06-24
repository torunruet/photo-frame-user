<!DOCTYPE html>
<html>

<head>
    <title>Render Images - FrameX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .frame-container {
            position: relative;
            width: 300px;
            height: 300px;
            overflow: hidden;
        }

        .main-photo {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            z-index: 10;
        }

        .frame-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: fill;
            pointer-events: none;
            z-index: 20;
        }

        .remove-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            z-index: 30;
            background-color: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.2s;
        }

        .remove-btn:hover {
            background-color: rgba(255, 0, 0, 0.9);
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-6">
    <div class="flex w-full max-w-7xl gap-8">
        <div class="flex flex-col gap-8">
            <div id="image-grid" class="grid grid-cols-1 md:grid-cols-2 gap-8 justify-items-center">
                @foreach ($images as $imgIdx => $image)
                    <div class="flex flex-col items-center bg-gray-100 rounded-2xl shadow-md p-4 w-80 relative">
                        <div class="frame-container mb-4">
                            <button type="button" class="remove-btn" data-img-idx="{{ $imgIdx }}">×</button>
                            <img id="main-img-{{ $imgIdx }}" src="{{ asset('storage/' . ltrim($image, '/')) }}" alt="Image"
                                class="main-photo" />
                            <img id="frame-overlay-{{ $imgIdx }}"
                                src="" alt="Frame Overlay"
                                class="frame-overlay" />
                        </div>

                        <div class="flex items-center gap-2 w-full mt-auto">
                            {{-- The buttons will be dynamically added here by the updateFrameThumbnails function --}}
                        </div>

                        <div class="text-sm mt-2 w-full text-left">
                            Quantity:
                            <div class="flex items-center mt-1 gap-2">
                                <button type="button" class="qty-minus px-2 py-1 bg-gray-300 text-black rounded"
                                    data-img-idx="{{ $imgIdx }}">−</button>
                                <input type="number" min="1" value="1"
                                    class="photo-qty text-center border px-2 py-1 rounded w-16"
                                    data-img-idx="{{ $imgIdx }}">
                                <button type="button" class="qty-plus px-2 py-1 bg-gray-300 text-black rounded"
                                    data-img-idx="{{ $imgIdx }}">+</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="w-full flex flex-col gap-4 mt-8">
                <div class="bg-white rounded-xl shadow p-4 text-lg">
                    <span id="summary-text">Amount: 1 Frame * {{ count($images) }} picture = 0 tk</span>
                </div>
                <div class="flex justify-between">
                    <a href="{{ route('front.view') }}"
                        class="bg-sky-500 text-white px-4 py-2 text-sm rounded-xl shadow hover:bg-sky-600 transition-all duration-200 flex flex-col items-center justify-center leading-tight h-16 text-center">
                        <span class="font-semibold">Start</span> <em>New Session</em>
                    </a>
                    <button id="next-btn"
                        class="px-8 py-3 bg-gray-400 border-2 border-gray-400 rounded-xl text-lg font-mono hover:bg-gray-100">
                        Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- No changes in the <head> or styling section --}}

    <script>
        let images = [...@json($images)];
        let frames = [];
        let selectedFrames = Array(images.length).fill(null);
        let selectedPrices = Array(images.length).fill(0);
        let selectedQuantities = Array(images.length).fill(1);
        const deviceId = '{{ session("authenticated_device")["device_id"] ?? "" }}'; // Get device_id from authenticated_device session

        // Function to fetch frames from API
        async function fetchFrames() {
            try {
                if (!deviceId) {
                    console.error('Device ID not found. Please login first.');
                    window.location.href = '{{ route("device.login") }}'; // Redirect to login if no device ID
                    return;
                }

                const response = await fetch(`http://127.0.0.1:8081/api/frames/device?device_id=${deviceId}`);
                const data = await response.json();

                if (data.status === "success") {
                    frames = data.data.frames.map(frame => ({
                        id: frame.id,
                        name: frame.name,
                        price: frame.price,
                        image_path: frame.image_path.startsWith('http')
                            ? frame.image_path
                            : `http://127.0.0.1:8081/storage/${frame.image_path}`
                    }));

                    // Update frame thumbnails
                    updateFrameThumbnails();

                    // Set default (first) frame for all images after fetching
                    if (frames.length > 0) {
                        selectedFrames = Array(images.length).fill(frames[0].id);
                        selectedPrices = Array(images.length).fill(Number(frames[0].price)); // Ensure price is number

                         // Iterate through each image and set the first frame as overlay
                        const frameContainers = document.querySelectorAll('#image-grid > .flex.flex-col.items-center');
                        frameContainers.forEach((container, imgIdx) => {
                            const frameOverlay = container.querySelector(`#frame-overlay-${imgIdx}`);
                            if (frameOverlay) {
                                frameOverlay.src = frames[0].image_path;
                            }
                        });

                        updateSummary();
                    }
                } else {
                    console.error('Error fetching frames:', data.message);
                }
            } catch (error) {
                console.error('Error fetching frames:', error);
            }
        }

        // Function to update frame thumbnails in the UI
        function updateFrameThumbnails() {
            // Target only the image grid containers
            const frameContainers = document.querySelectorAll('#image-grid > .flex.flex-col.items-center');

            frameContainers.forEach((container, imgIdx) => {
                // Find the frame buttons container within this specific image card
                let frameButtonsContainer = container.querySelector('.flex.items-center.gap-2');

                // This container should already exist based on the Blade template structure
                // If for some reason it doesn't, log an error and skip.
                if (!frameButtonsContainer) {
                    console.error('Frame buttons container not found for image card', imgIdx);
                    return;
                }

                frameButtonsContainer.innerHTML = ''; // Clear existing buttons

                frames.forEach((frame, frameIdx) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = `frame-thumb border-2 rounded-lg p-1 bg-gray-200 focus:ring-2 focus:ring-blue-400`;
                    button.dataset.imgIdx = imgIdx;
                    button.dataset.frameId = frame.id;
                    button.dataset.framePrice = frame.price;
                    button.dataset.frameImg = frame.image_path;

                    if (frameIdx === 0) {
                        button.style.borderColor = '#2563eb';
                        // Set initial frame overlay only if it hasn't been set by default in Blade
                        // or if we are refreshing frames.
                        const frameOverlay = document.getElementById('frame-overlay-' + imgIdx);
                        if (frameOverlay && frameOverlay.src === '' || frameOverlay.src.includes('($frames[0]->image_path ?? \'\')')) { // Check if src is empty or still has the placeholder
                             frameOverlay.src = frame.image_path;
                        }
                    }

                    button.innerHTML = `
                        <img src="${frame.image_path}" alt="${frame.name}" class="w-10 h-10 object-contain rounded" />
                        ${frame.price} tk
                    `;

                    button.addEventListener('click', function() {
                        const imgIdx = Number(this.dataset.imgIdx);
                        const frameId = this.dataset.frameId;
                        const framePrice = this.dataset.framePrice;
                        const frameImg = this.dataset.frameImg;

                        selectedFrames[imgIdx] = frameId;
                        selectedPrices[imgIdx] = Number(framePrice); // Convert to number

                        this.parentElement.querySelectorAll('button').forEach(b => b.style.borderColor = '#e5e7eb');
                        this.style.borderColor = '#2563eb';

                        const frameOverlay = document.getElementById('frame-overlay-' + imgIdx);
                        if (frameOverlay) {
                            frameOverlay.src = frameImg;
                        }
                        updateSummary();
                    });

                    frameButtonsContainer.appendChild(button);
                });

                // Add "More" button
                const moreButton = document.createElement('span');
                moreButton.className = 'ml-auto text-sm text-gray-500 cursor-pointer';
                moreButton.textContent = 'More';
                frameButtonsContainer.appendChild(moreButton);
            });
        }

        // Call fetchFrames when the page loads
        fetchFrames();

        function updateSummary() {
            const total = selectedPrices.reduce((sum, price, idx) => {
                const priceNum = Number(price) || 0;
                const qtyNum = Number(selectedQuantities[idx]) || 0;
                return sum + (priceNum * qtyNum);
            }, 0);
            const totalPics = selectedQuantities.reduce((a, b) => a + (Number(b) || 0), 0);
            document.getElementById('summary-text').textContent = `Amount: Frame * ${totalPics} picture(s) = ${total} tk`;
        }

        document.querySelectorAll('.frame-thumb').forEach(btn => {
            btn.addEventListener('click', function() {
                const imgIdx = Number(this.dataset.imgIdx);
                const frameId = this.dataset.frameId;
                const framePrice = this.dataset.framePrice;
                const frameImg = this.dataset.frameImg;

                selectedFrames[imgIdx] = frameId;
                selectedPrices[imgIdx] = Number(framePrice); // Convert to number

                this.parentElement.querySelectorAll('button').forEach(b => b.style.borderColor = '#e5e7eb');
                this.style.borderColor = '#2563eb';

                const frameOverlay = document.getElementById('frame-overlay-' + imgIdx);
                if (frameOverlay) {
                    frameOverlay.src = frameImg;
                }
                updateSummary();
            });
        });

        document.querySelectorAll('.photo-qty').forEach(input => {
            input.addEventListener('input', function() {
                const imgIdx = Number(this.dataset.imgIdx);
                let qty = Math.max(1, Number(this.value));
                selectedQuantities[imgIdx] = qty;
                updateSummary();
            });
        });

        document.querySelectorAll('.qty-plus').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = Number(this.dataset.imgIdx);
                const input = document.querySelector(`.photo-qty[data-img-idx="${idx}"]`);
                input.value = Number(input.value) + 1;
                selectedQuantities[idx] = Number(input.value);
                updateSummary();
            });
        });

        document.querySelectorAll('.qty-minus').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = Number(this.dataset.imgIdx);
                const input = document.querySelector(`.photo-qty[data-img-idx="${idx}"]`);
                input.value = Math.max(1, Number(input.value) - 1);
                selectedQuantities[idx] = Number(input.value);
                updateSummary();
            });
        });

        function updateRemoveButtons() {
            const removeButtons = document.querySelectorAll('.remove-btn');
            if (removeButtons.length <= 1) {
                removeButtons.forEach(btn => btn.classList.add('hidden'));
            } else {
                removeButtons.forEach(btn => btn.classList.remove('hidden'));
            }
        }

        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (document.querySelectorAll('.remove-btn').length <= 1) return;

                const imgIdx = Number(this.dataset.imgIdx);
                const card = this.closest('.flex.flex-col.items-center');
                if (card) card.remove();

                images.splice(imgIdx, 1);
                selectedFrames.splice(imgIdx, 1);
                selectedPrices.splice(imgIdx, 1);
                selectedQuantities.splice(imgIdx, 1);

                // Reassign data-img-idx for all remaining
                document.querySelectorAll('.flex.flex-col.items-center').forEach((el, newIdx) => {
                    el.querySelectorAll('[data-img-idx]').forEach(child => {
                        child.setAttribute('data-img-idx', newIdx);
                    });
                });

                updateSummary();
                updateRemoveButtons();
            });
        });

        updateSummary();
        updateRemoveButtons();

        document.getElementById('next-btn').addEventListener('click', function() {
            const mapping = images.map((image, idx) => ({
                image: image,
                frame_id: selectedFrames[idx],
                quantity: selectedQuantities[idx]
            }));

            const total = selectedPrices.reduce((sum, price, idx) => sum + (price * selectedQuantities[idx]), 0);

            fetch('/merge-frames', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        mapping
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fetch('/store-payment-data', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .content
                                },
                                body: JSON.stringify({
                                    mapping,
                                    total,
                                    merged_images: data.merged_images
                                })
                            })
                            .then(res => res.json())
                            .then(data2 => {
                                if (data2.success) {
                                    window.location.href = '/billing';
                                }
                            });
                    }
                });
        });
    </script>

</body>

</html>
