<?php

declare(strict_types=1);

namespace b1tc0re\LaravelFrankenCache;

use b1tc0re\LaravelFrankenCache\Test\Support\TieredCacheFake;

function franken_cache_tiered_get(string $key): string|false
{
    TieredCacheFake::recordFunction(__FUNCTION__, $key);

    return TieredCacheFake::$values[$key] ?? false;
}

function franken_cache_tiered_many(array $keys): array
{
    TieredCacheFake::recordFunction(__FUNCTION__, $keys);

    $values = [];

    foreach ($keys as $key) {
        if (array_key_exists($key, TieredCacheFake::$values)) {
            $values[$key] = TieredCacheFake::$values[$key];
        }
    }

    return $values;
}

function franken_cache_tiered_add(string $key, string $value, int $ttl): bool
{
    TieredCacheFake::recordFunction(__FUNCTION__, $key, $value, $ttl);

    if (array_key_exists($key, TieredCacheFake::$values)) {
        return false;
    }

    TieredCacheFake::$values[$key] = $value;

    return true;
}

function franken_cache_tiered_put_many(array $values, int $ttl): bool
{
    TieredCacheFake::recordFunction(__FUNCTION__, $values, $ttl);

    foreach ($values as $key => $value) {
        TieredCacheFake::$values[$key] = $value;
    }

    return true;
}

function franken_cache_tiered_set(string $key, string $value, int $ttl): bool
{
    TieredCacheFake::recordFunction(__FUNCTION__, $key, $value, $ttl);
    TieredCacheFake::$values[$key] = $value;

    return true;
}

function franken_cache_tiered_forever(string $key, string $value): bool
{
    TieredCacheFake::recordFunction(__FUNCTION__, $key, $value);
    TieredCacheFake::$values[$key] = $value;

    return true;
}

function franken_cache_tiered_forget(string $key): bool
{
    TieredCacheFake::recordFunction(__FUNCTION__, $key);

    $exists = array_key_exists($key, TieredCacheFake::$values);
    unset(TieredCacheFake::$values[$key]);

    return $exists;
}

function franken_cache_tiered_touch(string $key, int $ttl): bool
{
    TieredCacheFake::recordFunction(__FUNCTION__, $key, $ttl);

    return array_key_exists($key, TieredCacheFake::$values);
}

function franken_cache_tiered_flush(): bool
{
    TieredCacheFake::recordFunction(__FUNCTION__);
    TieredCacheFake::$values = [];

    return true;
}

function franken_cache_tiered_increment(string $key, int $value): int
{
    TieredCacheFake::recordFunction(__FUNCTION__, $key, $value);

    $result                        = (int) (TieredCacheFake::$values[$key] ?? 0) + $value;
    TieredCacheFake::$values[$key] = (string) $result;

    return $result;
}

function franken_cache_tiered_decrement(string $key, int $value): int
{
    TieredCacheFake::recordFunction(__FUNCTION__, $key, $value);

    $result                        = (int) (TieredCacheFake::$values[$key] ?? 0) - $value;
    TieredCacheFake::$values[$key] = (string) $result;

    return $result;
}
