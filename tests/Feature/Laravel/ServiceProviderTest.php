<?php

declare(strict_types=1);

use Laratusk\Spreedly\Resources\GatewayResource;
use Laratusk\Spreedly\SpreedlyClient;

test('service provider registers spreedly client', function (): void {
    $client = $this->app->make(SpreedlyClient::class);

    expect($client)->toBeInstanceOf(SpreedlyClient::class);
});

test('client is registered as singleton', function (): void {
    $client1 = $this->app->make(SpreedlyClient::class);
    $client2 = $this->app->make(SpreedlyClient::class);

    expect($client1)->toBe($client2);
});

test('client is also bound as spreedly alias', function (): void {
    $client = $this->app->make('spreedly');

    expect($client)->toBeInstanceOf(SpreedlyClient::class);
});

test('client has all resource properties', function (): void {
    $client = $this->app->make(SpreedlyClient::class);

    expect($client->gateways)->toBeInstanceOf(GatewayResource::class);
});
