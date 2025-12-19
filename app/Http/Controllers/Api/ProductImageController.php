<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function destroy($id)
    {
        $img = ProductImage::findOrFail($id);
        $img->delete();

        return response()->json(['message' => 'deleted']);
    }

    public function forceDestroy($id)
    {
        $img = ProductImage::withTrashed()->findOrFail($id);
        if ($img->image_url) {
            $path = ltrim(str_replace('/storage/', '', $img->image_url), '/');
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
        $img->forceDelete();

        return response()->json(['message' => 'permanently removed']);
    }
}
