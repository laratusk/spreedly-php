<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Testing;

use Laratusk\Spreedly\SpreedlyClient;

final readonly class SpreedlyFake
{
    public MockTransporter $mock;

    private SpreedlyClient $client;

    public function __construct()
    {
        $this->mock = new MockTransporter;
        $this->client = new SpreedlyClient(
            environmentKey: 'test',
            accessSecret: 'test',
            transporter: $this->mock,
        );
    }

    /**
     * Create a SpreedlyFake instance with an accessible MockTransporter.
     *
     * Usage in standalone PHP tests:
     * ```php
     * $fake = SpreedlyFake::make();
     * $fake->mock->addResponse('GET', 'gateways/token.json', $data);
     * $gateway = $fake->client()->gateways->retrieve('token');
     * $fake->mock->assertCalled('GET', 'gateways/token.json');
     * ```
     *
     * Usage in Laravel tests (swap the container binding):
     * ```php
     * $fake = SpreedlyFake::make();
     * $fake->mock->addResponse('GET', 'gateways/token.json', $data);
     * $this->app->instance(SpreedlyClient::class, $fake->client());
     * // Facade now uses the fake:
     * $gateway = Spreedly::gateways()->retrieve('token');
     * $fake->mock->assertCalled('GET', 'gateways/token.json');
     * ```
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * Get the underlying SpreedlyClient backed by the mock transporter.
     */
    public function client(): SpreedlyClient
    {
        return $this->client;
    }
}
