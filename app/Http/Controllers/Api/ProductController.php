<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\CoverImage;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TeakImage;
use App\Services\CacheService;
use App\Services\ProductImageService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductImageService $imageService
    ) {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 8);
        $limit = $request->get('limit');

        // Generate cache key from request parameters
        $cacheKey = CacheService::generateKey('products:index', [
            'per_page' => $perPage,
            'limit' => $limit,
            'q' => $request->q,
            'featured' => $request->boolean('featured') ? '1' : null,
            'category_id' => $request->category_id,
            'page' => $request->get('page', 1),
        ]);

        return CacheService::remember(
            $cacheKey,
            CacheService::TAG_PRODUCTS,
            CacheService::TTL_MEDIUM,
            function () use ($request, $perPage, $limit) {
                $query = Product::query()
                    ->select([
                        'id',
                        'name',
                        'description',
                        'material',
                        'is_featured',
                        'master_category_id',
                        'dimension_id',
                        'create_by',
                        'created_at',
                        'updated_at',
                    ])
                    ->with([
                        'masterCategory:id,name',
                        'dimension:id,width,height,depth',
                        'creator:id,name,email',
                        'coverImages:id,product_id,image_url',
                        'productImages' => fn($q) => $q->select(['id', 'product_id', 'image_url', 'alt', 'order'])->orderBy('order'),
                        'teakImages:id,product_id,image_url',
                    ]);

                // Simple search (instead of queryable for now)
                if ($request->filled('q')) {
                    $search = $request->q;
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'ilike', "%{$search}%")
                            ->orWhere('description', 'ilike', "%{$search}%");
                    });
                }

                // Filter featured products
                if ($request->boolean('featured')) {
                    $query->where('is_featured', true);
                }

                // Filter by category_id (legacy support)
                if ($request->filled('category_id')) {
                    $query->where('master_category_id', $request->category_id);
                }

                // If limit is specified, return limited collection (for carousel)
                if ($limit) {
                    return ApiResponse::success($query->take((int) $limit)->get());
                }

                return ApiResponse::success($query->paginate($perPage));
            }
        );
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['create_by'] = auth()->id();

        $product = Product::create($data);

        $this->handleImageUploads($product, $request);

        // Invalidate products cache
        CacheService::invalidate(CacheService::TAG_PRODUCTS);

        return ApiResponse::success(
            $product->load(['productImages', 'teakImages', 'coverImages', 'masterCategory', 'dimension', 'creator']),
            'Product created successfully',
            201
        );
    }

    public function show($id)
    {
        $cacheKey = CacheService::generateKey('products:show', ['id' => $id]);

        return CacheService::remember(
            $cacheKey,
            CacheService::TAG_PRODUCTS,
            CacheService::TTL_LONG,
            function () use ($id) {
                $product = Product::with(['productImages', 'teakImages', 'coverImages', 'masterCategory', 'dimension', 'creator'])
                    ->findOrFail($id);

                return ApiResponse::success($product);
            }
        );
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validated();

        // Hapus gambar yang spesifik
        if (!empty($data['product_images_delete'])) {
            $this->imageService->deleteImages(ProductImage::class, $data['product_images_delete']);
        }

        if (!empty($data['teak_images_delete'])) {
            $this->imageService->deleteImages(TeakImage::class, $data['teak_images_delete']);
        }

        if (!empty($data['cover_images_delete'])) {
            $this->imageService->deleteImages(CoverImage::class, $data['cover_images_delete']);
        }

        // Hapus key delete dari data sebelum update product
        unset($data['product_images_delete'], $data['teak_images_delete'], $data['cover_images_delete']);

        $product->update($data);

        // Simpan gambar baru
        $this->handleImageUploads($product, $request);

        // Invalidate products cache
        CacheService::invalidate(CacheService::TAG_PRODUCTS);

        return ApiResponse::success(
            $product->load(['productImages', 'teakImages', 'coverImages', 'masterCategory', 'dimension']),
            'Product updated successfully'
        );
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        $this->imageService->deleteAllImages($product);

        $product->delete();

        // Invalidate products cache
        CacheService::invalidate(CacheService::TAG_PRODUCTS);

        return ApiResponse::success(null, 'Product deleted successfully');
    }

    protected function handleImageUploads(Product $product, Request $request): void
    {
        if ($request->hasFile('product_images')) {
            $this->imageService->storeImages($product, $request->file('product_images'), 'product');
        }

        if ($request->hasFile('teak_images')) {
            $this->imageService->storeImages($product, $request->file('teak_images'), 'teak');
        }

        if ($request->hasFile('cover_images')) {
            $this->imageService->storeImages($product, $request->file('cover_images'), 'cover');
        }
    }
}
