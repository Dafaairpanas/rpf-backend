<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dimension extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['width', 'height', 'depth'];

    public function products()
    {
        return $this->hasMany(Product::class, 'dimension_id');
    }
}
