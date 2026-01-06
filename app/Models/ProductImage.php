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
    /**
     * Get full URL for image
     */
    public function getImageUrlAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        if (str_starts_with($value, 'http')) {
            // Fix for domain changes (e.g. localhost -> localhost:8000)
            $path = parse_url($value, PHP_URL_PATH);
            return url($path);
        }

        return asset('storage/' . $value);
    }
}
