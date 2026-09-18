# Laravel Franken Cache
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Test Status](https://github.com/b1tc0re/laravel-franken-cache/actions/workflows/tests.yml/badge.svg)](https://github.com/b1tc0re/laravel-franken-cache/actions/workflows/tests.yml)
[![Code Style Status](https://github.com/b1tc0re/laravel-franken-cache/actions/workflows/pint.yml/badge.svg)](https://github.com/b1tc0re/laravel-franken-cache/actions/workflows/pint.yml)
[![PHPStan](https://github.com/b1tc0re/laravel-franken-cache/actions/workflows/phpstan.yml/badge.svg)](https://github.com/b1tc0re/laravel-franken-cache/actions/workflows/phpstan.yml)


Laravel cache store adapter for the [FrankenPHP Tiered Cache](https://github.com/b1tc0re/frankenphp-tiered-cache) API.

The package exposes the native `franken_cache_tiered_*` functions through a
Laravel cache store named `franken`. It does not provide a PHP fallback storage
engine: the native API must be available in the FrankenPHP runtime when cache
operations are executed.

## Requirements

- PHP 8.4 (`^8.4`);
- `illuminate/cache` 12.x or 13.x;
- FrankenPHP with the tiered-cache API enabled.

The runtime must provide these functions:

- `franken_cache_tiered_get`;
- `franken_cache_tiered_many`;
- `franken_cache_tiered_set`;
- `franken_cache_tiered_put_many`;
- `franken_cache_tiered_forever`;
- `franken_cache_tiered_forget`;
- `franken_cache_tiered_flush`;
- `franken_cache_tiered_touch`;
- `franken_cache_tiered_add`;
- `franken_cache_tiered_increment`;
- `franken_cache_tiered_decrement`.

## Installation

```bash
composer require b1tc0re/laravel-franken-cache
```

The service provider is registered automatically through Laravel package
discovery.

If package discovery is disabled, register the provider manually:

```php
<?php

// bootstrap/providers.php

use b1tc0re\LaravelFrankenCache\ServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    ServiceProvider::class,
];
```

## Configuration

Add the store to `config/cache.php`:

```php
'stores' => [
    'franken' => [
        'driver' => 'franken',
        'prefix' => env('FRANKEN_CACHE_PREFIX', ''),
        'events' => false,
        'serializable_classes' => null,
    ],
],
```

The `prefix` is applied to every key before it is passed to FrankenPHP. If it
is omitted, the application cache prefix is used.

To make it the default Laravel store:

```dotenv
CACHE_STORE=franken
```

## Usage

Use the store through Laravel's cache facade:

```php
use Illuminate\Support\Facades\Cache;

Cache::store('franken')->put('user:1', ['name' => 'Dmitry'], 600);

$user = Cache::store('franken')->get('user:1');

Cache::store('franken')->forget('user:1');
```

The store supports the standard Laravel cache operations, including:

- `get`, `many`;
- `put`, `putMany`, `forever`;
- `add`, `forget`, `flush`, `touch`;
- `increment`, `decrement`.

## Serialization

Non-numeric values are stored using PHP serialization. Finite numeric values
are stored as plain values without serialization. As a result, numeric values
read from the native cache are returned as strings, while complex values are
restored to their original PHP types.

The `serializable_classes` option is passed to PHP's `unserialize()` through
the `allowed_classes` option.

## Testing

The unit tests use namespaced fakes for the native functions, so they do not
require a FrankenPHP build or a running FrankenPHP server:

```bash
composer install
composer test
```

Run all local quality checks and tests with:

```bash
composer global require --no-interaction --no-progress sebastian/phpcpd:6.0.3
export PATH="$(composer global config bin-dir --absolute):$PATH"
composer ci
```

This runs Pint, PHPCPD, PHPStan, Rector, and PHPUnit.

## License

MIT
