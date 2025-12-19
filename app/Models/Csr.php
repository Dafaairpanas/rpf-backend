<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Csr extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'create_by'];

    protected $appends = ['thumbnail_url'];

    public function content()
    {
        return $this->hasOne(CsrContent::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'create_by');
    }

    /**
     * Extract thumbnail URL dari content HTML (gambar pertama)
     * PENTING: Tidak auto-load content untuk menghindari N+1 query.
     * Pastikan controller sudah eager load 'content' sebelum serialize.
     */
    public function getThumbnailUrlAttribute()
    {
        // JANGAN auto-load! Biarkan controller yang eager load
        // Kalau content belum di-load, return null (performance safe)
        if (!$this->relationLoaded('content')) {
            return null;
        }

        $html = $this->content?->content ?? '';

        if (empty($html)) {
            return null;
        }

        // Extract first <img src="...">
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $html, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
