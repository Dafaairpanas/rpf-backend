<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public function products()
    {
        return $this->hasMany(Product::class, 'master_category_id');
    }
}
