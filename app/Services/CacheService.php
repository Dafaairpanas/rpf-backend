<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Cache Service
 * 
 * Centralized caching untuk API responses.
 * Menggunakan tags untuk invalidation yang bersih dan aman.
 */
class CacheService
{
    /**
     * Default TTL values (in seconds)
     */
    public const TTL_SHORT = 300;      // 5 minutes
    public const TTL_MEDIUM = 1800;    // 30 minutes
    public const TTL_LONG = 3600;      // 1 hour
    public const TTL_VERY_LONG = 86400; // 24 hours

    /**
     * Cache tags for different data types
     */
    public const TAG_PRODUCTS = 'products';
    public const TAG_NEWS = 'news';
    public const TAG_CSR = 'csr';
    public const TAG_BANNERS = 'banners';
    public const TAG_BRANDS = 'brands';
    public const TAG_CERTIFICATIONS = 'certifications';
    public const TAG_CATEGORIES = 'categories';
    public const TAG_DIMENSIONS = 'dimensions';

    /**
     * Check if current cache driver supports tags
     */
    public static function supportsTags(): bool
    {
        $driver = config('cache.default');
        return in_array($driver, ['redis', 'memcached', 'array']);
    }

    /**
     * Get cached data or execute callback and cache result
     */
    public static function remember(string $key, string $tag, int $ttl, callable $callback): mixed
    {
        // If driver doesn't support tags, use simple cache
        if (!self::supportsTags()) {
            return Cache::remember($key, $ttl, $callback);
        }

        return Cache::tags([$tag])->remember($key, $ttl, $callback);
    }

    /**
     * Invalidate cache by tag
     */
    public static function invalidate(string $tag): void
    {
        if (self::supportsTags()) {
            Cache::tags([$tag])->flush();
        } else {
            // For drivers without tags, we need to forget specific keys
            // This is a fallback - less efficient but works
            self::forgetByPrefix($tag);
        }
    }

    /**
     * Invalidate multiple tags
     */
    public static function invalidateMultiple(array $tags): void
    {
        foreach ($tags as $tag) {
            self::invalidate($tag);
        }
    }

    /**
     * Generate cache key from request parameters
     */
    public static function generateKey(string $prefix, array $params = []): string
    {
        // Filter out empty values and sort for consistency
        $params = array_filter($params, fn($v) => $v !== null && $v !== '');
        ksort($params);

        if (empty($params)) {
            return $prefix;
        }

        return $prefix . ':' . md5(serialize($params));
    }

    /**
     * Forget cache keys by prefix (fallback for non-tag drivers)
     * Note: This only works for file/database drivers with prefix scanning
     */
    private static function forgetByPrefix(string $prefix): void
    {
        // For database driver, we can query and delete
        if (config('cache.default') === 'database') {
            $table = config('cache.stores.database.table', 'cache');
            $cachePrefix = config('cache.prefix', '');

            \DB::table($table)
                ->where('key', 'like', $cachePrefix . $prefix . '%')
                ->delete();
        }

        // For file driver, we'd need to scan files - skip for simplicity
        // The cache will naturally expire
    }

    /**
     * Clear all API caches
     */
    public static function clearAll(): void
    {
        $tags = [
            self::TAG_PRODUCTS,
            self::TAG_NEWS,
            self::TAG_CSR,
            self::TAG_BANNERS,
            self::TAG_BRANDS,
            self::TAG_CERTIFICATIONS,
            self::TAG_CATEGORIES,
            self::TAG_DIMENSIONS,
        ];

        self::invalidateMultiple($tags);
    }
}
