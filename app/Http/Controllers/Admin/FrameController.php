<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FrameController extends Controller
{
    public function index()
    {
        $frames = Frame::latest()->paginate(10);
        return view('admin.frames.index', compact('frames'));
    }

    public function create()
    {
        return view('admin.frames.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $imagePath = $request->file('image')->store('frames', 'public');

        \Log::info('Frame image stored at: ' . $imagePath); // Log the file path

        Frame::create([
            'name' => $request->name,
            'image_path' => $imagePath,
            'category' => $request->category,
            'price' => $request->price,
            'is_active' => $request->is_active ?? true
        ]);

        return redirect()->route('admin.frames.index')
            ->with('success', 'Frame created successfully.');
    }

    public function edit(Frame $frame)
    {
        return view('admin.frames.edit', compact('frame'));
    }

    public function update(Request $request, Frame $frame)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            Storage::disk('public')->delete($frame->image_path);
            // Store new image
            $imagePath = $request->file('image')->store('frames', 'public');
            \Log::info('Frame image updated to: ' . $imagePath); // Log the file path
            $frame->image_path = $imagePath;
        }

        $frame->update([
            'name' => $request->name,
            'category' => $request->category,
            'price' => $request->price,
            'is_active' => $request->is_active ?? true
        ]);

        return redirect()->route('admin.frames.index')
            ->with('success', 'Frame updated successfully.');
    }

    public function destroy(Frame $frame)
    {
        Storage::disk('public')->delete($frame->image_path);
        $frame->delete();

        return redirect()->route('admin.frames.index')
            ->with('success', 'Frame deleted successfully.');
    }
}
