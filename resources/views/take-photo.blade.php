<!DOCTYPE html>
<html>
<head>
  <title>Photo Booth</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    video {
      width: 640px;
      height: 480px;
      border-radius: 12px;
      object-fit: cover;
    }

    .photo img {
      width: 80px;
      height: 60px;
    }

    .photo.selected {
      border: 2px solid #3b82f6;
    }

    #countdown {
      animation: pulse 1s infinite;
      font-size: 4rem;
      padding: 0.5rem 1rem;
      background-color: rgba(0, 0, 0, 0.6);
      color: white;
      border-radius: 12px;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 20;
    }

    @keyframes pulse {
      0% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
      50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.6; }
      100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
    }
  </style>
</head>
<body class="bg-black text-white flex flex-col items-center justify-center min-h-screen relative">

  <!-- Countdown Display -->
  <div id="countdown" class="hidden"></div>

  <!-- Video Feed -->
  <div class="relative mb-6">
    <video id="video" autoplay playsinline class="shadow-lg"></video>

    <!-- Centered Camera Icon Button -->
    <button id="capture-btn" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-white p-4 rounded-full shadow-xl hover:bg-gray-200" title="Start Photo Session">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h4l2-3h6l2 3h4v13H3V7z" />
      </svg>
    </button>
  </div>

  <!-- Captured Photos -->
  <div id="photos" class="flex flex-wrap gap-2 justify-center mb-3"></div>

  <!-- Buttons -->
  <div class="flex gap-3 mb-5">
    <button onclick="history.back()" class="bg-gray-600 text-white px-4 py-2 text-sm rounded hover:bg-gray-700">
      Back
    </button>

    <button id="next-btn" disabled class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold px-5 py-2 text-sm rounded shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
      Next
    </button>

    <button id="single-capture-btn" class="bg-blue-600 text-white px-4 py-2 text-sm rounded hover:bg-blue-700">
      Capture
    </button>
  </div>

  <!-- Form -->
  <form id="photoForm" method="POST" action="{{ route('upload.photos') }}">
    @csrf
    <input type="hidden" name="session" value="{{ $session }}">
    <input type="hidden" name="images" id="imagesInput">
  </form>

  <!-- JavaScript -->
  <script>
    const video = document.getElementById("video");
    const captureBtn = document.getElementById("capture-btn");
    const photosContainer = document.getElementById("photos");
    const nextBtn = document.getElementById("next-btn");
    const singleCaptureBtn = document.getElementById("single-capture-btn");
    const imagesInput = document.getElementById("imagesInput");
    const countdownEl = document.getElementById("countdown");

    let selectedImages = [];

    // Access camera
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(stream => video.srcObject = stream)
      .catch(err => {
        Swal.fire({
          icon: 'error',
          title: 'Camera Error',
          text: 'Camera access denied or not available.'
        });
        console.error(err);
      });

    // Countdown capture
    captureBtn.addEventListener("click", () => {
      let countdown = 5;
      captureBtn.style.display = "none";
      countdownEl.textContent = countdown;
      countdownEl.classList.remove("hidden");

      const countdownInterval = setInterval(() => {
        countdown--;
        countdownEl.textContent = countdown;

        if (countdown <= 0) {
          clearInterval(countdownInterval);
          countdownEl.classList.add("hidden");

          capturePhoto();

          let photoCount = 1;
          const interval = setInterval(() => {
            if (photoCount >= 4) {
              clearInterval(interval);
              captureBtn.disabled = false;
              return;
            }
            capturePhoto();
            photoCount++;
          }, 2000);
        }
      }, 1000);
    });

    // Manual capture
    singleCaptureBtn.addEventListener("click", () => {
      capturePhoto();
    });

    // Photo capture function
    function capturePhoto() {
      const canvas = document.createElement("canvas");
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      const ctx = canvas.getContext("2d");
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
      const dataURL = canvas.toDataURL("image/png");

      // Container for image and delete button
      const photoDiv = document.createElement("div");
      photoDiv.classList.add("photo", "border", "rounded", "cursor-pointer", "relative");

      const img = document.createElement("img");
      img.src = dataURL;
      photoDiv.appendChild(img);

      // Delete (×) icon
      const deleteBtn = document.createElement("button");
      deleteBtn.innerHTML = "&times;";
      deleteBtn.className = "absolute top-0 right-0 bg-black text-white rounded-full text-xs w-5 h-5 flex items-center justify-center transform translate-x-1/2 -translate-y-1/2 hover:bg-red-600 z-10";
      deleteBtn.title = "Remove photo";

      deleteBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        photoDiv.remove();
        selectedImages = selectedImages.filter(src => src !== dataURL);
        nextBtn.disabled = selectedImages.length === 0;
      });

      photoDiv.appendChild(deleteBtn);

      // Toggle selection
      photoDiv.addEventListener("click", () => {
        if (photoDiv.classList.contains("selected")) {
          photoDiv.classList.remove("selected");
          selectedImages = selectedImages.filter(src => src !== dataURL);
        } else {
          if (selectedImages.length >= 4) {
            Swal.fire({
              icon: 'warning',
              title: 'Limit Reached',
              text: 'You can select a maximum of 4 images.',
              timer: 2000,
              showConfirmButton: false
            });
            return;
          }
          photoDiv.classList.add("selected");
          selectedImages.push(dataURL);
        }
        nextBtn.disabled = selectedImages.length === 0;
      });

      photosContainer.appendChild(photoDiv);
    }

    // Submit selected photos
    nextBtn.addEventListener("click", () => {
      if (selectedImages.length === 0) {
        Swal.fire({
          icon: 'info',
          title: 'No Photos Selected',
          text: 'Please select at least one photo before proceeding.'
        });
        return;
      }
      imagesInput.value = JSON.stringify(selectedImages);
      document.getElementById("photoForm").submit();
    });
  </script>
</body>
</html>
