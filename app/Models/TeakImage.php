<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeakImage extends Model
{
    use HasFactory;

    protected $fillable = ['image_url', 'product_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

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

        return asset('storage/' . ltrim($value, '/'));
    }
}
