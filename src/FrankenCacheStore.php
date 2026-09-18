<?php

declare(strict_types=1);

namespace b1tc0re\LaravelFrankenCache;

use Exception;
use Illuminate\Cache\TaggableStore;

final class FrankenCacheStore extends TaggableStore
{
    /**
     * The classes that should be allowed during unserialization.
     *
     * @var array|bool|null
     */
    private $serializableClasses;

    /**
     * A string that should be prepended to keys.
     */
    private string $prefix;

    /**
     * Create a new FrankenCache store.
     *
     * @param  array|bool|null  $serializableClasses
     */
    public function __construct(string $prefix = '', $serializableClasses = null)
    {
        $this->serializableClasses = $serializableClasses;

        $this->setPrefix($prefix);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     */
    public function get($key): mixed
    {
        $value = franken_cache_tiered_get($this->prefix.$key);

        return $value !== false ? $this->unserialize($value) : null;
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     */
    public function many(array $keys): array
    {
        if ($keys === []) {
            return [];
        }

        $values = franken_cache_tiered_many(array_map(function ($key) {
            return $this->prefix.$key;
        }, $keys));

        $results = [];

        foreach ($keys as $key) {
            $prefixedKey = $this->prefix.$key;
            $value       = $values[$prefixedKey] ?? false;

            $results[$key] = $value === false
                ? null
                : $this->unserialize($value);
        }

        return $results;
    }

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $seconds
     */
    public function put($key, $value, $seconds): bool
    {
        return franken_cache_tiered_set(
            $this->prefix.$key,
            (string) $this->serialize($value),
            $seconds,
        );
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     *
     * @param  int  $seconds
     */
    public function putMany(array $values, $seconds): bool
    {
        if ($values === []) {
            return false;
        }

        $serialized = [];

        foreach ($values as $key => $value) {
            $serialized[$this->prefix.$key] = (string) $this->serialize($value);
        }

        return franken_cache_tiered_put_many(
            $serialized,
            $seconds,
        );
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed  $value
     */
    public function forever($key, $value): bool
    {
        return franken_cache_tiered_forever($this->prefix.$key, (string) $this->serialize($value));
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     */
    public function forget($key): bool
    {
        return franken_cache_tiered_forget($this->prefix.$key);
    }

    /**
     * Remove all items from the cache.
     */
    public function flush(): bool
    {
        return franken_cache_tiered_flush();
    }

    /**
     * Set the expiration of a cached item.
     *
     * @param  string  $key
     * @param  int  $seconds
     */
    public function touch($key, $seconds): bool
    {
        return franken_cache_tiered_touch($this->prefix.$key, $seconds);
    }

    /**
     * Store an item in the cache if the key doesn't exist.
     */
    public function add(string $key, mixed $value, int $seconds): bool
    {
        return franken_cache_tiered_add(
            $this->prefix.$key,
            (string) $this->serialize($value),
            $seconds,
        );
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed  $value
     *
     * @throws Exception
     */
    public function increment($key, $value = 1): int
    {
        return franken_cache_tiered_increment($this->prefix.$key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed  $value
     *
     * @throws Exception
     */
    public function decrement($key, $value = 1): int
    {
        return franken_cache_tiered_decrement($this->prefix.$key, $value);
    }

    /**
     * Set the cache key prefix.
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * Get the cache key prefix.
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Determine if the given value should be stored as plain value.
     */
    protected function shouldBeStoredWithoutSerialization(mixed $value): bool
    {
        return is_numeric($value) && is_finite($value);
    }

    /**
     * Serialize the value.
     */
    private function serialize(mixed $value): mixed
    {
        return $this->shouldBeStoredWithoutSerialization($value) ? $value : serialize($value);
    }

    /**
     * Unserialize the given value.
     */
    private function unserialize(string $value): mixed
    {
        if (is_numeric($value)) {
            return $value;
        }

        if ($this->serializableClasses !== null) {
            return unserialize($value, ['allowed_classes' => $this->serializableClasses]);
        }

        return unserialize($value);
    }
}
