<?php

namespace App\Services;

use App\Models\CoverImage;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TeakImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductImageService
{
    /**
     * Store multiple images for a product
     */
    public function storeImages(Product $product, array $images, string $type): void
    {
        foreach ($images as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store("products/{$product->id}/{$type}", 'public');
            $url = Storage::url($path);

            $attributes = [
                'image_url' => $url,
                'product_id' => $product->id,
            ];

            match ($type) {
                'product' => ProductImage::create($attributes),
                'teak' => TeakImage::create($attributes),
                'cover' => CoverImage::create($attributes),
            };
        }
    }

    /**
     * Delete images by IDs
     */
    public function deleteImages(string $modelClass, array $imageIds): void
    {
        $images = $modelClass::whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            // Extract path from URL (assuming /storage/ link)
            $path = str_replace('/storage/', 'public/', $image->image_url);

            // Or simpler regex if URL struct might vary, but for default storage link:
            // $path = str_replace(url('/storage') . '/', '', $image->image_url);
            // Let's stick to the controller logic but standardized

            // Basic check if it's a relative path starting with storage/ or absolute URL
            $relativePath = str_replace(Storage::url(''), '', $image->image_url);

            // If the str_replace didn't change anything (e.g. if Storage::url('') returns /storage/)
            // and the image_url was /storage/foo.jpg, we get foo.jpg

            // Let's rely on the previous logic which was:
            // $path = str_replace('/storage/', 'public/', $image->image_url);
            // But using 'public' disk means we should pass relative path to delete()
            // e.g. "products/1/product/filename.jpg"

            // The previous code was:
            // $path = str_replace('/storage/', 'public/', $image->image_url);
            // Storage::delete($path);
            // If filesystem is local, 'public/' prefix might be needed if root is storage/app
            // But usually Storage::disk('public')->delete('path/to/file');

            // Let's stick to the working logic from controller but ensure we use the correct disk
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            } elseif (Storage::exists($path)) {
                Storage::delete($path);
            }
        }

        $modelClass::whereIn('id', $imageIds)->delete();
    }

    /**
     * Delete all images associated with a product
     */
    public function deleteAllImages(Product $product): void
    {
        // We need to load them if not loaded, but service shouldn't assume lazy loading.
        // Better to query IDs.

        $this->deleteImages(ProductImage::class, $product->productImages()->pluck('id')->toArray());
        $this->deleteImages(TeakImage::class, $product->teakImages()->pluck('id')->toArray());
        $this->deleteImages(CoverImage::class, $product->coverImages()->pluck('id')->toArray());
    }
}
