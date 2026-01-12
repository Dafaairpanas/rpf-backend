<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brands extends Model
{
    protected $fillable = ['name', 'image_url'];

    /**
     * Get full URL for image
     */
    public function getImageUrlAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        // Already a full URL
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        // Already has /storage/ prefix
        if (str_starts_with($value, '/storage/')) {
            return url($value);
        }

        // Path like "storage/brands/..." needs asset() helper
        if (str_starts_with($value, 'storage/')) {
            return asset($value);
        }

        return asset('storage/' . ltrim($value, '/'));
    }
}
