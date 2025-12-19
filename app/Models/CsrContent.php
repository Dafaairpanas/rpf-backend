<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsrContent extends Model
{
    use HasFactory;

    protected $fillable = ['csr_id', 'content'];

    public function csr()
    {
        return $this->belongsTo(Csr::class);
    }
}
