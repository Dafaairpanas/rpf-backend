<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Certifications;
use App\Services\CacheService;
use Illuminate\Http\Request;

class CertificationController extends Controller
{
    // Constants removed as they are now in CacheService

    public function index()
    {
        return CacheService::remember(
            'certifications:list',
            CacheService::TAG_CERTIFICATIONS,
            CacheService::TTL_MEDIUM,
            function () {
                return Certifications::all();
            }
        );
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

        // Invalidate cache
        CacheService::invalidate(CacheService::TAG_CERTIFICATIONS);

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

        // Invalidate cache
        CacheService::invalidate(CacheService::TAG_CERTIFICATIONS);

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

        // Invalidate cache
        CacheService::invalidate(CacheService::TAG_CERTIFICATIONS);

        return ApiResponse::success(null, 'Certification deleted successfully');
    }
}
