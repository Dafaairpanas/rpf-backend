<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Upload image for editor (News/CSR content)
     * 
     * @bodyParam image file required Image file to upload. Max 5MB. Allowed: jpg, jpeg, png, gif, webp
     * @bodyParam folder string Optional folder path. Default: editor-images
     * 
     * @response 201 {
     *   "success": true,
     *   "message": "Image uploaded successfully",
     *   "data": {
     *     "url": "https://example.com/storage/editor-images/abc123.webp",
     *     "path": "editor-images/abc123.webp",
     *     "filename": "abc123.webp",
     *     "size": 102400,
     *     "mime_type": "image/webp"
     *   }
     * }
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120', // 5MB max
            'folder' => 'nullable|string|max:100',
        ]);

        $file = $request->file('image');
        $folder = $request->get('folder', 'editor-images');

        // Sanitize folder name
        $folder = preg_replace('/[^a-zA-Z0-9\-_\/]/', '', $folder);

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;

        // Store file
        $path = $file->storeAs($folder, $filename, 'public');

        // Generate public URL
        $url = Storage::disk('public')->url($path);

        return ApiResponse::success([
            'url' => $url,
            'path' => $path,
            'filename' => $filename,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ], 'Image uploaded successfully', 201);
    }

    /**
     * Delete uploaded image
     * 
     * @bodyParam path string required The path of the image to delete
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Image deleted successfully"
     * }
     */
    public function deleteImage(Request $request)
    {
        $request->validate([
            'path' => 'required|string|max:255',
        ]);

        $path = $request->input('path');

        // Security: only allow deletion within editor-images folder
        if (!Str::startsWith($path, 'editor-images/')) {
            return ApiResponse::error('Invalid path', 403);
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return ApiResponse::success(null, 'Image deleted successfully');
        }

        return ApiResponse::error('Image not found', 404);
    }

    /**
     * Upload multiple images at once
     * 
     * @bodyParam images file[] required Array of image files. Max 5 images, each max 5MB
     * @bodyParam folder string Optional folder path. Default: editor-images
     * 
     * @response 201 {
     *   "success": true,
     *   "message": "3 images uploaded successfully",
     *   "data": [
     *     { "url": "...", "path": "...", "filename": "..." },
     *     { "url": "...", "path": "...", "filename": "..." }
     *   ]
     * }
     */
    public function uploadMultiple(Request $request)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'folder' => 'nullable|string|max:100',
        ]);

        $folder = $request->get('folder', 'editor-images');
        $folder = preg_replace('/[^a-zA-Z0-9\-_\/]/', '', $folder);

        $uploaded = [];

        foreach ($request->file('images') as $file) {
            $extension = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;
            $path = $file->storeAs($folder, $filename, 'public');
            $url = Storage::disk('public')->url($path);

            $uploaded[] = [
                'url' => $url,
                'path' => $path,
                'filename' => $filename,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ];
        }

        return ApiResponse::success(
            $uploaded,
            count($uploaded) . ' image(s) uploaded successfully',
            201
        );
    }
}
