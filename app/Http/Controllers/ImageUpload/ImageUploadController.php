<?php

namespace App\Http\Controllers\ImageUpload;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Events\RefreshHomepage;
use App\Events\PhotoUploaded;

class ImageUploadController extends Controller
{
    public function index()
    {
        $images = collect(Storage::disk('public')->files('uploads'))->filter(function ($file) {
            return in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']);
        });

        return view('main-view', ['images' => $images]);
    }

    public function QrView()
    {
        $sessionId = Str::uuid();

        return view('QrCodeSection.QrCode', [
            'sessionId' => $sessionId,
            'uploadUrl' => route('upload.mobile', ['session' => $sessionId])
        ]);
    }

    public function uploadMultiple($session, Request $request)
{
    \Log::info('Upload request received', [
        'session' => $session,
        'files' => $request->file('photos'),
    ]);

    $request->validate([
        'photos.*' => 'required|image|max:10000', // 10MB max per image
    ]);

    $uploadPath = "uploads/{$session}";
    if (!Storage::disk('public')->exists($uploadPath)) {
        \Log::info('Creating directory', ['path' => $uploadPath]);
        Storage::disk('public')->makeDirectory($uploadPath);
    }

    $uploadedPaths = [];
    foreach ($request->file('photos') as $photo) {
        \Log::info('Processing file', [
            'originalName' => $photo->getClientOriginalName(),
            'mimeType' => $photo->getMimeType(),
            'tempPath' => $photo->getPathname(),
        ]);

        try {
            $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $path = $photo->storeAs($uploadPath, $filename, 'public');

            if (!$path) {
                \Log::error('Failed to save file', ['file' => $filename, 'path' => $uploadPath]);
                return response()->json(['status' => 'error', 'message' => 'Failed to save file.'], 500);
            }

            \Log::info('File saved successfully', ['path' => $path]);
            $uploadedPaths[] = $path;

            broadcast(new PhotoUploaded($session, $path));
        } catch (\Exception $e) {
            \Log::error('Exception while saving file', [
                'file' => $photo->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred while saving the file.'], 500);
        }
    }

    \Log::info('Files uploaded successfully', ['paths' => $uploadedPaths]);

    // Broadcast the event to refresh the homepage with the session ID
    broadcast(new RefreshHomepage($session));

    // Redirect to renderingImage page using the new controller
    return redirect()->route('rendering.image', ['session' => $session]);
}


    public function broadcastRefresh()
    {
        broadcast(new RefreshHomepage());
        return response()->json(['status' => 'ok', 'message' => 'Homepage refresh event broadcasted.']);
    }
}

