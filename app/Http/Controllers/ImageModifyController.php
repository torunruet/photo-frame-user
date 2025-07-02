<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Frame;
use Illuminate\Support\Str;
use Imagick;
use ImagickException;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageModifyController extends Controller
{
    public function ShowImage($session)
    {
        $images = Storage::disk('public')->files("uploads/{$session}");
        $frames = Frame::where('is_active', true)->get();

        // 💡 Send response with no-cache headers
        return response()
            ->view('ImageRender.renderingImage', [
                'images' => $images,
                'session' => $session,
                'frames' => $frames
            ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function mergeFrames(Request $request)
    {
        $mergedImageUrls = [];
        $errors = [];

        // ✅ Proper setup for Intervention Image v3 using GD driver
        $manager = new ImageManager(new Driver());

        foreach ($request->mapping as $item) {
            try {
                $imagePath = storage_path('app/public/' . $item['image']);

                // Get frame from external API
                $deviceId = session('authenticated_device')['device_id'] ?? null;
                if (!$deviceId) {
                    $errors[] = "Device ID not found in session";
                    continue;
                }

                $apiResponse = file_get_contents("http://127.0.0.1:8081/api/frames/device?device_id={$deviceId}");
                $apiData = json_decode($apiResponse, true);

                if ($apiData['status'] !== 'success') {
                    $errors[] = "Failed to fetch frames from API";
                    continue;
                }

                $frame = collect($apiData['data']['frames'])->firstWhere('id', $item['frame_id']);
                if (!$frame) {
                    $errors[] = "Frame not found for ID: {$item['frame_id']}";
                    continue;
                }

                if (!file_exists($imagePath)) {
                    $errors[] = "Image not found at path: {$imagePath}";
                    continue;
                }

                // Download frame image from external API
                $frameImageUrl = "http://127.0.0.1:8081/storage/{$frame['image_path']}";
                $frameImageContent = file_get_contents($frameImageUrl);
                if (!$frameImageContent) {
                    $errors[] = "Failed to download frame image from: {$frameImageUrl}";
                    continue;
                }

                // Save frame image temporarily
                $tempFramePath = storage_path('app/public/temp_frame_' . uniqid() . '.png');
                file_put_contents($tempFramePath, $frameImageContent);

                // Read frame and get dimensions
                $frameOverlay = $manager->read($tempFramePath);
                $frameWidth = $frameOverlay->width();
                $frameHeight = $frameOverlay->height();

                // Read main image
                $mainImage = $manager->read($imagePath);

                // ✅ Resize the image to cover the frame completely
                $ratio = max(
                    $frameWidth / $mainImage->width(),
                    $frameHeight / $mainImage->height()
                );

                $resizeWidth = intval($mainImage->width() * $ratio);
                $resizeHeight = intval($mainImage->height() * $ratio);

                $resizedImage = $mainImage->resize($resizeWidth, $resizeHeight)
                    ->crop(
                        $frameWidth,
                        $frameHeight,
                        intval(($resizeWidth - $frameWidth) / 2),
                        intval(($resizeHeight - $frameHeight) / 2)
                    );

                // ✅ Place frame over the resized image
                $resizedImage->place($frameOverlay, 'top-left', 0, 0);

                // ✅ Save the merged image
                $filename = 'merged/' . uniqid() . '.jpg';
                Storage::disk('public')->put($filename, (string) $resizedImage->toJpeg());

                $mergedImageUrls[] = asset('storage/' . $filename);

                // Clean up temporary frame file
                @unlink($tempFramePath);
            } catch (\Exception $e) {
                $errors[] = "Error processing image: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => count($mergedImageUrls) > 0,
            'merged_images' => $mergedImageUrls,
            'errors' => $errors
        ]);
    }

    public function uploadPhotos(Request $request)
    {
        $session = $request->input('session');
        $images = json_decode($request->input('images'), true);

        foreach ($images as $index => $imageData) {
            $image = str_replace('data:image/png;base64,', '', $imageData);
            $image = str_replace(' ', '+', $image);
            $imageName = "photo_{$index}_" . time() . ".png";

            Storage::disk('public')->put("uploads/{$session}/{$imageName}", base64_decode($image));
        }
        return redirect()->route('rendering.image', ['session' => $session]);
    }
}




