<?php

declare(strict_types=1);

namespace b1tc0re\LaravelFrankenCache\Test;

use b1tc0re\LaravelFrankenCache\ServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.stores.franken', [
            'driver' => 'franken',
            'prefix' => 'test:',
            'events' => false,
        ]);
    }
}
