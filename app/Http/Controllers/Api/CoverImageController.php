<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoverImage;
use Illuminate\Support\Facades\Storage;

class CoverImageController extends Controller
{
    public function destroy($id)
    {
        $img = CoverImage::findOrFail($id);
        $img->delete();

        return response()->json(['message' => 'deleted']);
    }

    public function forceDestroy($id)
    {
        $img = CoverImage::withTrashed()->findOrFail($id);
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
