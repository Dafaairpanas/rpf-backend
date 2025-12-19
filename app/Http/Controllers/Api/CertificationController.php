<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Certifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CertificationController extends Controller
{
    private const CACHE_KEY = 'certifications:list';
    private const CACHE_TTL = 300; // 5 menit

    public function index()
    {
        $certifications = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Certifications::all();
        });

        return ApiResponse::success($certifications);
    }

    // POST - Create certification
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageUrl = null;

        // Handle file upload
        if ($request->hasFile('image_url')) {
            $file = $request->file('image_url');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('certifications', $filename, 'public');
            $imageUrl = 'storage/certifications/' . $filename;
        }

        $certification = Certifications::create([
            'name' => $validated['name'],
            'image_url' => $imageUrl,
        ]);

        Cache::forget(self::CACHE_KEY);

        return ApiResponse::success($certification, 'Certification created successfully', 201);
    }

    // GET - Show certification detail
    public function show(Certifications $certification)
    {
        return ApiResponse::success($certification);
    }

    // PUT - Update certification
    public function update(Request $request, Certifications $certification)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageUrl = $certification->image_url;

        // Handle file upload
        if ($request->hasFile('image_url')) {
            // Hapus file lama jika ada
            if ($certification->image_url && file_exists(public_path($certification->image_url))) {
                unlink(public_path($certification->image_url));
            }

            $file = $request->file('image_url');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('certifications', $filename, 'public');
            $imageUrl = 'storage/certifications/' . $filename;
        }

        $certification->update([
            'name' => $validated['name'],
            'image_url' => $imageUrl,
        ]);

        Cache::forget(self::CACHE_KEY);

        return ApiResponse::success($certification, 'Certification updated successfully');
    }

    // DELETE - Hapus certification
    public function destroy(Certifications $certification)
    {
        // Hapus file jika ada
        if ($certification->image_url && file_exists(public_path($certification->image_url))) {
            unlink(public_path($certification->image_url));
        }

        $certification->delete();

        Cache::forget(self::CACHE_KEY);

        return ApiResponse::success(null, 'Certification deleted successfully');
    }
}
