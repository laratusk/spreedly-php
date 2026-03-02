<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Tests;

use Laratusk\Spreedly\Laravel\SpreedlyServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class LaravelTestCase extends BaseTestCase
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [SpreedlyServiceProvider::class];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('spreedly.environment_key', 'test_env_key');
        $app['config']->set('spreedly.access_secret', 'test_access_secret');
        $app['config']->set('spreedly.options', [
            'base_url' => 'https://core.spreedly.com/v1/',
            'timeout' => 30,
            'connect_timeout' => 10,
            'retries' => 0,
        ]);
        $app['config']->set('spreedly.mac_address_enabled', true);
        $app['config']->set('spreedly.mac_address_command', "ifconfig en0 | awk '/ether/{print $2}'");
    }
}
