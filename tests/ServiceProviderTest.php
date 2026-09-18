<?php

declare(strict_types=1);

namespace b1tc0re\LaravelFrankenCache\Test;

use b1tc0re\LaravelFrankenCache\FrankenCacheStore;
use Illuminate\Cache\Repository;

final class ServiceProviderTest extends TestCase
{
    public function test_franken_driver_is_registered_with_the_configured_prefix(): void
    {
        $repository = $this->app['cache']->store('franken');

        $this->assertInstanceOf(Repository::class, $repository);
        $this->assertInstanceOf(FrankenCacheStore::class, $repository->getStore());
        $this->assertSame('test:', $repository->getStore()->getPrefix());
    }
}
