<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Audit Log Model
 * 
 * Menyimpan log aktivitas untuk keamanan dan compliance.
 * Mencatat login attempts, CRUD operations, dan security events.
 */
class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'performed_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'performed_at' => 'datetime',
    ];

    /**
     * Relationship to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a login attempt
     */
    public static function logLogin(string $email, bool $success, ?int $userId = null): self
    {
        return self::create([
            'user_id' => $userId,
            'action' => $success ? 'login_success' : 'login_failed',
            'model_type' => 'App\\Models\\User',
            'description' => $success
                ? "User {$email} logged in successfully"
                : "Failed login attempt for {$email}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }

    /**
     * Log a logout
     */
    public static function logLogout(int $userId): self
    {
        return self::create([
            'user_id' => $userId,
            'action' => 'logout',
            'model_type' => 'App\\Models\\User',
            'description' => 'User logged out',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }

    /**
     * Log CRUD operation
     */
    public static function logCrud(
        string $action,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'description' => ucfirst($action) . ' ' . class_basename($model) . ' #' . $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }

    /**
     * Log security event
     */
    public static function logSecurity(string $event, string $description): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => 'security_' . $event,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }

    /**
     * Scope: recent logs
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('performed_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: by action type
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: failed logins
     */
    public function scopeFailedLogins($query)
    {
        return $query->where('action', 'login_failed');
    }

    /**
     * Scope: by IP address
     */
    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }
}
