<?php

declare(strict_types=1);

namespace b1tc0re\LaravelFrankenCache;

use Illuminate\Contracts\Foundation\Application;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class ServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-franken-cache');
    }

    public function register(): void
    {
        parent::register();

        $this->app->booting(function (): void {
            $this->app->make('cache')->extend(
                'franken',
                function (Application $app, array $config) {
                    return $app->make('cache')->repository(
                        new FrankenCacheStore(
                            (string) ($config['prefix']
                                ?? $app['config']->get('cache.prefix', '')),
                            $config['serializable_classes']
                            ?? $app['config']->get('cache.serializable_classes'),
                        ),
                        $config,
                    );
                },
            );
        });
    }
}
