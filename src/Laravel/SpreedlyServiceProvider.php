<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Laravel;

use Laratusk\Spreedly\SpreedlyClient;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Laravel service provider for the Spreedly SDK.
 * Uses spatie/laravel-package-tools for automatic config publishing.
 */
final class SpreedlyServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('spreedly')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(SpreedlyClient::class, static fn ($app): SpreedlyClient => new SpreedlyClient(
            environmentKey: (string) config('spreedly.environment_key', ''),
            accessSecret: (string) config('spreedly.access_secret', ''),
            options: (array) config('spreedly.options', []),
        ));

        $this->app->alias(SpreedlyClient::class, 'spreedly');
    }
}
