<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Brands;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BrandsController extends Controller
{
    private const CACHE_KEY = 'brands:list';
    private const CACHE_TTL = 300; // 5 menit

    public function index()
    {
        $brands = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Brands::all();
        });

        return ApiResponse::success($brands);
    }

    // POST - Create brand
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
            $file->storeAs('brands', $filename, 'public');
            $imageUrl = 'storage/brands/' . $filename;
        }

        $brand = Brands::create([
            'name' => $validated['name'],
            'image_url' => $imageUrl,
        ]);

        Cache::forget(self::CACHE_KEY);

        return ApiResponse::success($brand, 'Brand created successfully', 201);
    }

    // GET - Show brand detail
    public function show(Brands $brand)
    {
        return ApiResponse::success($brand);
    }

    // PUT - Update brand
    public function update(Request $request, Brands $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageUrl = $brand->image_url;

        // Handle file upload
        if ($request->hasFile('image_url')) {
            // Hapus file lama jika ada
            if ($brand->image_url && file_exists(public_path($brand->image_url))) {
                unlink(public_path($brand->image_url));
            }

            $file = $request->file('image_url');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('brands', $filename, 'public');
            $imageUrl = 'storage/brands/' . $filename;
        }

        $brand->update([
            'name' => $validated['name'],
            'image_url' => $imageUrl,
        ]);

        Cache::forget(self::CACHE_KEY);

        return ApiResponse::success($brand, 'Brand updated successfully');
    }

    // DELETE - Hapus brand
    public function destroy(Brands $brand)
    {
        // Hapus file jika ada
        if ($brand->image_url && file_exists(public_path($brand->image_url))) {
            unlink(public_path($brand->image_url));
        }

        $brand->delete();

        Cache::forget(self::CACHE_KEY);

        return ApiResponse::success(null, 'Brand deleted successfully');
    }
}
