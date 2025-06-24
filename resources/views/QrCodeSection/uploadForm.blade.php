<!DOCTYPE html>
<html>

<head>
    <title>FrameX - Upload Photos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>

<body class="bg-gray-50 flex flex-col items-center justify-center min-h-screen p-6">

    <h1 id="displayMessage" class="text-2xl font-bold mb-4">Upload Your Photos</h1>

    <form id="uploadForm" action="{{ route('upload.mobile', ['session' => $sessionId]) }}" method="POST"
        enctype="multipart/form-data" class="flex flex-col items-center w-full max-w-md">
        @csrf

        <input id="photoInput" type="file" accept="image/*" multiple
            class="mb-4 border border-gray-300 rounded-lg p-2 w-full">

        <p id="displayInfoMessage" class="text-gray-600 mb-4 text-center">You can upload a maximum of 4 images. Each
            must be
            under 10MB.</p>

        <!-- Preview grid -->
        <div id="previewContainer" class="grid grid-cols-2 gap-4 mb-4 w-full justify-items-center">
        </div>

        <button id="submitBtn" type="submit"
            class="px-4 py-2 bg-blue-500 text-white rounded-lg w-full disabled:opacity-50">Upload</button>

        <!-- Loader -->
        <div id="loader" class="hidden mt-4 text-blue-600 font-semibold">Uploading...</div>
    </form>

    <div id="thankYouMessage" class="hidden mt-4 text-green-600 font-semibold">Thank you for uploading!</div>

    <script>
        const MAX_IMAGES = 4;
        const MAX_SIZE_MB = 10;
        const photoInput = document.getElementById('photoInput');
        const previewContainer = document.getElementById('previewContainer');
        const uploadForm = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');
        const thankYouMessage = document.getElementById('thankYouMessage');
        const displayMessage = document.getElementById('displayMessage');
        const displayInfoMessage = document.getElementById('displayInfoMessage');
        const loader = document.getElementById('loader');

        let selectedImages = [];

        function renderPreviews() {
            previewContainer.innerHTML = '';

            selectedImages.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = "relative w-40 h-40";

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = "w-full h-full object-cover border border-gray-300 rounded";

                    const removeBtn = document.createElement('button');
                    removeBtn.textContent = '×';
                    removeBtn.className =
                        "absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 text-sm flex items-center justify-center";
                    removeBtn.onclick = () => {
                        selectedImages.splice(index, 1);
                        renderPreviews();
                    };

                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    previewContainer.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });

            photoInput.disabled = selectedImages.length >= MAX_IMAGES;
        }

        photoInput.addEventListener('change', () => {
            const newFiles = Array.from(photoInput.files);

            for (const file of newFiles) {
                if (file.size > MAX_SIZE_MB * 1024 * 1024) {
                    Toastify({
                        text: `Each image must be less than ${MAX_SIZE_MB}MB.`,
                        duration: 3000,
                        gravity: "top",
                        position: "center",
                        backgroundColor: "#f87171"
                    }).showToast();
                    return;
                }
            }

            if (selectedImages.length + newFiles.length > MAX_IMAGES) {
                Toastify({
                    text: `You can upload a maximum of ${MAX_IMAGES} images.`,
                    duration: 3000,
                    gravity: "top",
                    position: "center",
                    backgroundColor: "#f87171"
                }).showToast();
                return;
            }

            selectedImages.push(...newFiles);
            renderPreviews();
            photoInput.value = ''; // reset input
        });

        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault(); // VERY IMPORTANT ✅

            if (selectedImages.length === 0) {
                Toastify({
                    text: 'Please choose at least 1 image to upload.',
                    duration: 3000,
                    gravity: "top",
                    position: "center",
                    backgroundColor: "#f87171"
                }).showToast();
                return;
            }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            selectedImages.forEach(file => formData.append('photos[]', file));

            submitBtn.disabled = true;
            loader.classList.remove('hidden');

            try {
                const response = await fetch(uploadForm.action, {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    thankYouMessage.classList.remove('hidden');
                    previewContainer.innerHTML = '';
                    selectedImages = [];
                    photoInput.disabled = true;
                    photoInput.classList.add('hidden');
                    submitBtn.classList.add('hidden');
                    displayMessage.classList.add('hidden');
                    displayInfoMessage.classList.add('hidden');
                    await fetch('{{ route('broadcast.refresh') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    // No redirect; just show the thank you message and stay on the page
                } else {
                    throw new Error('Upload failed');
                }
            } catch (err) {
                console.error(err);
                Toastify({
                    text: 'Upload failed. Please try again.',
                    duration: 3000,
                    gravity: "top",
                    position: "center",
                    backgroundColor: "#f87171"
                }).showToast();
            } finally {
                submitBtn.disabled = false;
                loader.classList.add('hidden');
            }
        });
    </script>
</body>

</html>
