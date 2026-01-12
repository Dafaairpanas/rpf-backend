<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['image_url', 'alt', 'order', 'product_id'];

    protected $casts = [
        'order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

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

        // Already has /storage/ prefix (from Storage::url())
        if (str_starts_with($value, '/storage/')) {
            return url($value);
        }

        // Raw path without prefix
        return asset('storage/' . ltrim($value, '/'));
    }
}
