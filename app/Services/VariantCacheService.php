<?php

namespace App\Services;

use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductVariant;
use App\Modules\Products\Models\ProductVariantOption;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VariantCacheService
{
    // Cache keys
    const CACHE_KEYS = [
        'PRODUCTS_WITH_VARIANTS' => 'variants:products_with_variants:',
        'VARIANT_DETAILS' => 'variants:details:',
        'LOW_STOCK_VARIANTS' => 'variants:low_stock',
        'OUT_OF_STOCK_VARIANTS' => 'variants:out_of_stock',
        'VARIANT_OPTIONS' => 'variants:options',
        'INVENTORY_VALUATION' => 'variants:inventory_valuation',
        'VARIANT_SEARCH' => 'variants:search:',
        'VARIANT_PERFORMANCE' => 'variants:performance:',
    ];

    // Cache TTL in minutes
    const CACHE_TTL = [
        'SHORT' => 5,     // 5 minutes for frequently changing data
        'MEDIUM' => 30,   // 30 minutes for moderately changing data
        'LONG' => 120,    // 2 hours for rarely changing data
        'DAILY' => 1440,  // 24 hours for daily reports
    ];

    /**
     * Get products with variants for POS (cached)
     */
    public static function getProductsWithVariantsForPos(?int $categoryId = null, ?string $search = null): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = self::CACHE_KEYS['PRODUCTS_WITH_VARIANTS'] . md5(json_encode([
            'category_id' => $categoryId,
            'search' => $search,
            'store_id' => auth()->user()?->currentStoreId()
        ]));

        return Cache::remember($cacheKey, self::CACHE_TTL['SHORT'], function() use ($categoryId, $search) {
            return OptimizedVariantService::getProductsWithVariantsForPos($categoryId, $search);
        });
    }

    /**
     * Get variant details (cached)
     */
    public static function getVariantDetails(int $variantId): ?ProductVariant
    {
        $cacheKey = self::CACHE_KEYS['VARIANT_DETAILS'] . $variantId;

        return Cache::remember($cacheKey, self::CACHE_TTL['MEDIUM'], function() use ($variantId) {
            return OptimizedVariantService::getVariantDetails($variantId);
        });
    }

    /**
     * Get low stock variants (cached)
     */
    public static function getLowStockVariants(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(self::CACHE_KEYS['LOW_STOCK_VARIANTS'], self::CACHE_TTL['SHORT'], function() {
            return OptimizedVariantService::getLowStockVariants();
        });
    }

    /**
     * Get out of stock variants (cached)
     */
    public static function getOutOfStockVariants(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(self::CACHE_KEYS['OUT_OF_STOCK_VARIANTS'], self::CACHE_TTL['SHORT'], function() {
            return OptimizedVariantService::getOutOfStockVariants();
        });
    }

    /**
     * Get variant options with values (cached)
     */
    public static function getVariantOptionsWithValues(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(self::CACHE_KEYS['VARIANT_OPTIONS'], self::CACHE_TTL['LONG'], function() {
            return OptimizedVariantService::getVariantOptionsWithValues();
        });
    }

    /**
     * Get inventory valuation (cached)
     */
    public static function getInventoryValuation(): array
    {
        return Cache::remember(self::CACHE_KEYS['INVENTORY_VALUATION'], self::CACHE_TTL['MEDIUM'], function() {
            return OptimizedVariantService::getVariantInventoryValuation();
        });
    }

    /**
     * Search variants (cached)
     */
    public static function searchVariants(string $query, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = self::CACHE_KEYS['VARIANT_SEARCH'] . md5($query . $limit);

        return Cache::remember($cacheKey, self::CACHE_TTL['MEDIUM'], function() use ($query, $limit) {
            return OptimizedVariantService::searchVariants($query, $limit);
        });
    }

    /**
     * Get variant performance metrics (cached)
     */
    public static function getVariantPerformanceMetrics(int $days = 30): array
    {
        $cacheKey = self::CACHE_KEYS['VARIANT_PERFORMANCE'] . $days;

        return Cache::remember($cacheKey, self::CACHE_TTL['MEDIUM'], function() use ($days) {
            return OptimizedVariantService::getVariantPerformanceMetrics($days);
        });
    }

    /**
     * Invalidate variant-related caches
     */
    public static function invalidateVariantCaches(?int $variantId = null): void
    {
        // Invalidate specific variant cache
        if ($variantId) {
            Cache::forget(self::CACHE_KEYS['VARIANT_DETAILS'] . $variantId);
        }

        // Invalidate general variant caches
        Cache::forget(self::CACHE_KEYS['LOW_STOCK_VARIANTS']);
        Cache::forget(self::CACHE_KEYS['OUT_OF_STOCK_VARIANTS']);
        Cache::forget(self::CACHE_KEYS['INVENTORY_VALUATION']);

        // Invalidate search caches (pattern-based deletion)
        $searchKeys = Cache::getRedis()?->keys(self::CACHE_KEYS['VARIANT_SEARCH'] . '*');
        if ($searchKeys) {
            foreach ($searchKeys as $key) {
                Cache::forget($key);
            }
        }

        // Invalidate performance caches
        for ($i = 7; $i <= 90; $i += 7) { // Common day ranges
            Cache::forget(self::CACHE_KEYS['VARIANT_PERFORMANCE'] . $i);
        }
    }

    /**
     * Invalidate product variants cache
     */
    public static function invalidateProductVariantsCache(?int $productId = null): void
    {
        // Invalidate POS product caches
        $posKeys = Cache::getRedis()?->keys(self::CACHE_KEYS['PRODUCTS_WITH_VARIANTS'] . '*');
        if ($posKeys) {
            foreach ($posKeys as $key) {
                Cache::forget($key);
            }
        }

        // Invalidate inventory valuation as it depends on product variants
        Cache::forget(self::CACHE_KEYS['INVENTORY_VALUATION']);
    }

    /**
     * Warm up variant caches
     */
    public static function warmUpVariantCaches(): void
    {
        try {
            // Warm up frequently accessed data
            self::getLowStockVariants();
            self::getOutOfStockVariants();
            self::getVariantOptionsWithValues();
            self::getInventoryValuation();

            // Warm up performance metrics for common ranges
            self::getVariantPerformanceMetrics(7);  // Last week
            self::getVariantPerformanceMetrics(30); // Last month

            // Warm up POS data for common scenarios
            self::getProductsWithVariantsForPos();
            self::getProductsWithVariantsForPos(null, 'tshirt'); // Common search

        } catch (\Exception $e) {
            \Log::warning('Variant cache warm-up failed: ' . $e->getMessage());
        }
    }

    /**
     * Get cache statistics for monitoring
     */
    public static function getCacheStatistics(): array
    {
        $redis = Cache::getRedis();
        if (!$redis) {
            return ['error' => 'Redis not available'];
        }

        $variantKeys = array_filter($redis->keys('*variant*'), function($key) {
            return str_contains($key, 'variants:');
        });

        $stats = [
            'total_variant_keys' => count($variantKeys),
            'keys_by_type' => [],
            'memory_usage' => 0,
            'oldest_key' => null,
            'newest_key' => null,
        ];

        foreach (self::CACHE_KEYS as $type => $pattern) {
            $typeKeys = array_filter($variantKeys, function($key) use ($pattern) {
                return str_starts_with($key, $pattern);
            });
            $stats['keys_by_type'][$type] = count($typeKeys);
        }

        // Get memory usage and key ages
        if (!empty($variantKeys)) {
            $pipe = $redis->pipeline();
            foreach ($variantKeys as $key) {
                $pipe->memory('usage', $key);
                $pipe->object('idletime', $key);
            }
            $results = $pipe->execute();

            $totalMemory = 0;
            $oldestTime = 0;
            $newestTime = 0;

            for ($i = 0; $i < count($variantKeys); $i += 2) {
                $memory = $results[$i] ?? 0;
                $idleTime = $results[$i + 1] ?? 0;

                $totalMemory += $memory;
                $oldestTime = max($oldestTime, $idleTime);
                $newestTime = min($newestTime, $idleTime);
            }

            $stats['memory_usage'] = $totalMemory;
            $stats['oldest_key_age_seconds'] = $oldestTime;
            $stats['newest_key_age_seconds'] = $newestTime;
        }

        return $stats;
    }

    /**
     * Clear all variant caches (for maintenance)
     */
    public static function clearAllVariantCaches(): int
    {
        $redis = Cache::getRedis();
        if (!$redis) {
            return 0;
        }

        $variantKeys = array_filter($redis->keys('*variant*'), function($key) {
            return str_contains($key, 'variants:');
        });

        $deletedCount = 0;
        foreach ($variantKeys as $key) {
            if (Cache::forget($key)) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Get variant data with automatic cache invalidation on updates
     */
    public static function getCachedVariantWithInvalidation(int $variantId): ?ProductVariant
    {
        $variant = self::getVariantDetails($variantId);

        if ($variant) {
            // Register cache invalidation on model updates
            $variant->saved(function ($model) {
                self::invalidateVariantCaches($model->id);
            });

            $variant->deleted(function ($model) {
                self::invalidateVariantCaches($model->id);
            });
        }

        return $variant;
    }

    /**
     * Cache variant search results with smart invalidation
     */
    public static function cacheSearchResults(string $query, \Illuminate\Database\Eloquent\Collection $results): void
    {
        $cacheKey = self::CACHE_KEYS['VARIANT_SEARCH'] . md5($query);
        Cache::put($cacheKey, $results, self::CACHE_TTL['MEDIUM']);

        // Store search key for invalidation
        $searchKeysKey = 'variant_search_keys';
        $searchKeys = Cache::get($searchKeysKey, []);
        $searchKeys[] = $cacheKey;
        Cache::put($searchKeysKey, array_unique($searchKeys), self::CACHE_TTL['LONG']);
    }

    /**
     * Invalidate all search result caches
     */
    public static function invalidateSearchCaches(): void
    {
        $searchKeys = Cache::get('variant_search_keys', []);
        foreach ($searchKeys as $key) {
            Cache::forget($key);
        }
        Cache::forget('variant_search_keys');
    }
}