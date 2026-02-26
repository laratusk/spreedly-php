<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\Gateway;
use Laratusk\Spreedly\DataTransferObjects\Transaction;
use Laratusk\Spreedly\Resources\GatewayResource;

test('create sends POST request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('gateways/create.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('gateways.json', ['gateway' => ['gateway_type' => 'stripe', 'login' => 'sk_test_xxx']])
        ->andReturn($fixture);

    $resource = new GatewayResource($transporter);
    $gateway = $resource->create(['gateway_type' => 'stripe', 'login' => 'sk_test_xxx']);

    expect($gateway)->toBeInstanceOf(Gateway::class);
    expect($gateway->token)->toBe('6DqX57I6fHgIuUkVnmUGPA3xoVyB');
    expect($gateway->gatewayType)->toBe('stripe');
    expect($gateway->state)->toBe('retained');
});

test('retrieve sends GET request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('gateways/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('gateways/6DqX57I6fHgIuUkVnmUGPA3xoVyB.json')
        ->andReturn($fixture);

    $resource = new GatewayResource($transporter);
    $gateway = $resource->retrieve('6DqX57I6fHgIuUkVnmUGPA3xoVyB');

    expect($gateway)->toBeInstanceOf(Gateway::class);
    expect($gateway->token)->toBe('6DqX57I6fHgIuUkVnmUGPA3xoVyB');
    expect($gateway->description)->toBe('My Stripe Gateway');
});

test('list returns paginated collection', function (): void {
    $fixture = $this->loadFixture('gateways/list.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('gateways.json', ['order' => 'desc'])
        ->andReturn($fixture);

    $resource = new GatewayResource($transporter);
    $collection = $resource->list();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(2);
    expect($collection->items[0])->toBeInstanceOf(Gateway::class);
    expect($collection->items[0]->token)->toBe('6DqX57I6fHgIuUkVnmUGPA3xoVyB');
    expect($collection->items[1]->token)->toBe('ABC123DEFGHIJKLMNOPabcdefgh');
});

test('list passes since_token for pagination', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('gateways.json', ['order' => 'desc', 'since_token' => 'some_token'])
        ->andReturn(['gateways' => []]);

    $resource = new GatewayResource($transporter);
    $collection = $resource->list('some_token');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
});

test('update sends PUT request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('gateways/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('put')
        ->once()
        ->with('gateways/6DqX57I6fHgIuUkVnmUGPA3xoVyB.json', ['gateway' => ['description' => 'Updated']])
        ->andReturn($fixture);

    $resource = new GatewayResource($transporter);
    $gateway = $resource->update('6DqX57I6fHgIuUkVnmUGPA3xoVyB', ['description' => 'Updated']);

    expect($gateway)->toBeInstanceOf(Gateway::class);
});

test('redact sends PUT request to redact endpoint', function (): void {
    $fixture = $this->loadFixture('gateways/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('put')
        ->once()
        ->with('gateways/6DqX57I6fHgIuUkVnmUGPA3xoVyB/redact.json')
        ->andReturn($fixture);

    $resource = new GatewayResource($transporter);
    $gateway = $resource->redact('6DqX57I6fHgIuUkVnmUGPA3xoVyB');

    expect($gateway)->toBeInstanceOf(Gateway::class);
});

test('retain sends PUT request to retain endpoint', function (): void {
    $fixture = $this->loadFixture('gateways/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('put')
        ->once()
        ->with('gateways/6DqX57I6fHgIuUkVnmUGPA3xoVyB/retain.json')
        ->andReturn($fixture);

    $resource = new GatewayResource($transporter);
    $gateway = $resource->retain('6DqX57I6fHgIuUkVnmUGPA3xoVyB');

    expect($gateway)->toBeInstanceOf(Gateway::class);
});

test('transactions returns paginated collection of gateway transactions', function (): void {
    $txFixture = $this->loadFixture('transactions/purchase.json')['transaction'];

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('gateways/6DqX57I6fHgIuUkVnmUGPA3xoVyB/transactions.json', ['order' => 'desc'])
        ->andReturn(['transactions' => [$txFixture]]);

    $resource = new GatewayResource($transporter);
    $collection = $resource->transactions('6DqX57I6fHgIuUkVnmUGPA3xoVyB');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(1);
    expect($collection->items[0])->toBeInstanceOf(Transaction::class);
});

test('transactions passes since_token for pagination', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('gateways/gw_token/transactions.json', ['order' => 'desc', 'since_token' => 'tok123'])
        ->andReturn(['transactions' => []]);

    $resource = new GatewayResource($transporter);
    $collection = $resource->transactions('gw_token', 'tok123');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(0);
});

test('supportedGateways returns raw array from API', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('gateways_options.json')
        ->andReturn(['gateways' => [['gateway_type' => 'stripe'], ['gateway_type' => 'braintree']]]);

    $resource = new GatewayResource($transporter);
    $result = $resource->supportedGateways();

    expect($result)->toBeArray();
    expect($result['gateways'])->toHaveCount(2);
});
