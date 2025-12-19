<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsContent extends Model
{
    use HasFactory;

    protected $fillable = ['news_id', 'content'];

    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
