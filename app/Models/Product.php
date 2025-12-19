<?php

namespace App\Models;

use App\Traits\Queryable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, Queryable;

    protected $fillable = [
        'name',
        'description',
        'material',
        'is_featured',
        'master_category_id',
        'dimension_id',
        'create_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    /**
     * Columns searchable via ?q= parameter
     */
    protected $searchable = ['name', 'description', 'material'];

    /**
     * Columns filterable via ?filter[key]= parameter
     */
    protected $filterable = ['master_category_id', 'dimension_id', 'is_featured'];

    /**
     * Columns sortable via ?sort= parameter
     */
    protected $sortable = ['id', 'name', 'created_at', 'updated_at'];

    /**
     * Scope: featured products only
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function masterCategory()
    {
        return $this->belongsTo(MasterCategory::class, 'master_category_id');
    }

    public function dimension()
    {
        return $this->belongsTo(Dimension::class, 'dimension_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'create_by');
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    public function teakImages()
    {
        return $this->hasMany(TeakImage::class);
    }

    public function coverImages()
    {
        return $this->hasMany(CoverImage::class);
    }

    protected static function booted()
    {
        static::deleting(function (self $product) {
            if ($product->isForceDeleting()) {
                $product->productImages()->forceDelete();
                $product->teakImages()->forceDelete();
                $product->coverImages()->forceDelete();
            } else {
                $product->productImages()->delete();
                $product->teakImages()->delete();
                $product->coverImages()->delete();
            }
        });

        static::restoring(function (self $product) {
            $product->productImages()->withTrashed()->restore();
            $product->teakImages()->withTrashed()->restore();
            $product->coverImages()->withTrashed()->restore();
        });
    }
}

