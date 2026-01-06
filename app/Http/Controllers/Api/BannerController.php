<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Public endpoint - get active banners sorted by order
     */
    public function index()
    {
        return CacheService::remember(
            'banners:index',
            CacheService::TAG_BANNERS,
            CacheService::TTL_SHORT, // 5 menit karena banner mungkin sering berubah
            function () {
                $banners = Banner::active()
                    ->ordered()
                    ->get();
                return ApiResponse::success(BannerResource::collection($banners));
            }
        );
    }

    /**
     * Admin - get all banners with pagination
     */
    public function adminIndex(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);

        // Cache admin index juga agar cepat
        $cacheKey = CacheService::generateKey('banners:admin', [
            'per_page' => $perPage,
            'page' => $request->get('page', 1),
            'q' => $request->q,
            'is_active' => $request->get('filter.is_active'),
        ]);

        return CacheService::remember(
            $cacheKey,
            CacheService::TAG_BANNERS,
            CacheService::TTL_SHORT,
            function () use ($request, $perPage) {
                $query = Banner::query()->ordered();

                // Search by title
                if ($request->filled('q')) {
                    $query->where('title', 'like', '%' . $request->q . '%');
                }

                // Filter by active status
                if ($request->has('filter.is_active')) {
                    $query->where('is_active', $request->boolean('filter.is_active'));
                }

                return ApiResponse::success(BannerResource::collection($query->paginate($perPage)));
            }
        );
    }

    /**
     * Admin - store new banner
     */
    public function store(StoreBannerRequest $request)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('banners', 'public');
        }

        // Set default order if not provided
        if (!isset($data['order'])) {
            $data['order'] = Banner::max('order') + 1;
        }

        unset($data['image']);
        $banner = Banner::create($data);

        // Invalidate cache
        CacheService::invalidate(CacheService::TAG_BANNERS);

        return ApiResponse::success(new BannerResource($banner), 'Banner created successfully', 201);
    }

    /**
     * Admin - show single banner
     */
    public function show($id)
    {
        $banner = Banner::findOrFail($id);

        return ApiResponse::success(new BannerResource($banner));
    }

    /**
     * Admin - update banner
     */
    public function update(UpdateBannerRequest $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $data['image_path'] = $request->file('image')->store('banners', 'public');
        }

        unset($data['image']);
        $banner->update($data);

        // Invalidate cache
        CacheService::invalidate(CacheService::TAG_BANNERS);

        return ApiResponse::success(new BannerResource($banner), 'Banner updated successfully');
    }

    /**
     * Admin - delete banner
     */
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        // Delete image file
        if ($banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }

        $banner->delete();

        // Invalidate cache
        CacheService::invalidate(CacheService::TAG_BANNERS);

        return ApiResponse::success(null, 'Banner deleted successfully');
    }
}
