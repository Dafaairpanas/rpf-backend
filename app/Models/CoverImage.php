<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoverImage extends Model
{
    use HasFactory;

    protected $fillable = ['image_url', 'product_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute($value): ?string
    {
        if (!$value)
            return null;
        if (str_starts_with($value, 'http')) {
            $path = parse_url($value, PHP_URL_PATH);
            return url($path);
        }
        return asset('storage/' . $value);
    }
}
