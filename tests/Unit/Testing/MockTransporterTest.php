<?php

declare(strict_types=1);

use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\SpreedlyClient;
use Laratusk\Spreedly\Testing\MockTransporter;
use Laratusk\Spreedly\Testing\SpreedlyFake;

test('can add and retrieve responses', function (): void {
    $transporter = new MockTransporter;
    $transporter->addResponse('GET', 'gateways.json', ['gateways' => []]);

    $result = $transporter->get('gateways.json');

    expect($result)->toBe(['gateways' => []]);
});

test('get records the call', function (): void {
    $transporter = new MockTransporter;
    $transporter->get('gateways.json');

    $transporter->assertCalled('GET', 'gateways.json');
    expect($transporter->getCallCount())->toBe(1);
});

test('post records the call', function (): void {
    $transporter = new MockTransporter;
    $transporter->post('gateways.json', ['gateway' => []]);

    $transporter->assertCalled('POST', 'gateways.json');
    expect($transporter->getCallCount())->toBe(1);
});

test('put records the call', function (): void {
    $transporter = new MockTransporter;
    $transporter->put('gateways/token.json');

    $transporter->assertCalled('PUT', 'gateways/token.json');
    expect($transporter->getCallCount())->toBe(1);
});

test('patch records the call', function (): void {
    $transporter = new MockTransporter;
    $transporter->patch('gateways/token.json');

    $transporter->assertCalled('PATCH', 'gateways/token.json');
    expect($transporter->getCallCount())->toBe(1);
});

test('delete records the call', function (): void {
    $transporter = new MockTransporter;
    $transporter->delete('gateways/token.json');

    $transporter->assertCalled('DELETE', 'gateways/token.json');
    expect($transporter->getCallCount())->toBe(1);
});

test('get call count returns correct number', function (): void {
    $transporter = new MockTransporter;
    $transporter->get('endpoint1.json');
    $transporter->get('endpoint2.json');
    $transporter->post('endpoint3.json');

    expect($transporter->getCallCount())->toBe(3);
});

test('assertCalled throws when not called', function (): void {
    $transporter = new MockTransporter;

    expect(fn () => $transporter->assertCalled('GET', 'gateways.json'))
        ->toThrow(RuntimeException::class);
});

test('returns empty array when no response configured', function (): void {
    $transporter = new MockTransporter;
    $result = $transporter->get('unconfigured.json');

    expect($result)->toBe([]);
});

test('getRaw returns empty string by default', function (): void {
    $transporter = new MockTransporter;
    $result = $transporter->getRaw('transcripts/token.json');

    expect($result)->toBe('');
});

test('can be used with SpreedlyClient', function (): void {
    $transporter = new MockTransporter;
    $transporter->addResponse('GET', 'gateways.json', ['gateways' => []]);

    $client = new SpreedlyClient('test', 'test', $transporter);
    $collection = $client->gateways->list();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
});

test('addResponse returns self for chaining', function (): void {
    $transporter = new MockTransporter;
    $result = $transporter->addResponse('GET', 'endpoint.json', []);

    expect($result)->toBe($transporter);
});

test('SpreedlyFake make returns SpreedlyFake with accessible mock', function (): void {
    $fake = SpreedlyFake::make();

    expect($fake)->toBeInstanceOf(SpreedlyFake::class);
    expect($fake->mock)->toBeInstanceOf(MockTransporter::class);
    expect($fake->client())->toBeInstanceOf(SpreedlyClient::class);
    expect($fake->client()->gateways)->toBeInstanceOf(\Laratusk\Spreedly\Resources\GatewayResource::class);
});

test('SpreedlyFake mock can configure and return responses', function (): void {
    $fake = SpreedlyFake::make();
    $fake->mock->addResponse('GET', 'gateways.json', ['gateways' => []]);

    $collection = $fake->client()->gateways->list();

    expect($collection)->toBeInstanceOf(\Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection::class);
    expect($collection->count())->toBe(0);
    $fake->mock->assertCalled('GET', 'gateways.json');
});

test('SpreedlyFake mock tracks calls made through client', function (): void {
    $fake = SpreedlyFake::make();
    $fake->mock->addResponse('POST', 'gateways.json', [
        'gateway' => [
            'token' => 'gw_test_token',
            'gateway_type' => 'test',
            'name' => 'Test',
            'state' => 'retained',
            'payment_methods' => [],
            'characteristics' => [],
            'credentials' => [],
            'gateway_settings' => [],
            'gateway_specific_fields' => [],
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
        ],
    ]);

    $gateway = $fake->client()->gateways->create(['gateway_type' => 'test']);

    expect($gateway->token)->toBe('gw_test_token');
    $fake->mock->assertCalled('POST', 'gateways.json');
    expect($fake->mock->getCallCount())->toBe(1);
});
