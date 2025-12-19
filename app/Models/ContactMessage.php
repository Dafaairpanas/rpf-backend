<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'product_id',
        'status',
    ];

    /**
     * Relationship to Product (for "Order Now" feature)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Status constants
     */
    public const STATUS_NEW = 'new';
    public const STATUS_READ = 'read';
    public const STATUS_REPLIED = 'replied';

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for new messages only
     */
    public function scopeUnread($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }
}
