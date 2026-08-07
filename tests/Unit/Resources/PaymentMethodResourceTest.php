<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\PaymentMethod;
use Laratusk\Spreedly\DataTransferObjects\PaymentMethodEvent;
use Laratusk\Spreedly\DataTransferObjects\Transaction;
use Laratusk\Spreedly\Resources\PaymentMethodResource;

test('create sends POST request with payment method wrapper', function (): void {
    $fixture = $this->loadFixture('payment_methods/create.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('payment_methods.json', ['payment_method' => ['credit_card' => ['number' => '4111111111111111']]])
        ->andReturn($fixture);

    $resource = new PaymentMethodResource($transporter);
    $pm = $resource->create(['credit_card' => ['number' => '4111111111111111']]);

    expect($pm)->toBeInstanceOf(PaymentMethod::class);
    expect($pm->token)->toBe('56wyNnSmuA6en32YnlLFoJNFLSI');
    expect($pm->lastFourDigits)->toBe('1111');
    expect($pm->cardType)->toBe('visa');
});

test('retrieve sends GET request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('payment_methods/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI.json')
        ->andReturn($fixture);

    $resource = new PaymentMethodResource($transporter);
    $pm = $resource->retrieve('56wyNnSmuA6en32YnlLFoJNFLSI');

    expect($pm)->toBeInstanceOf(PaymentMethod::class);
    expect($pm->storageState)->toBe('retained');
});

test('list returns paginated collection', function (): void {
    $fixture = ['payment_methods' => [$this->loadFixture('payment_methods/show.json')['payment_method']]];

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods.json', ['order' => 'desc'])
        ->andReturn($fixture);

    $resource = new PaymentMethodResource($transporter);
    $collection = $resource->list();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(1);
});

test('retain returns transaction', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('put')
        ->once()
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/retain.json', [])
        ->andReturn($fixture);

    $resource = new PaymentMethodResource($transporter);
    $tx = $resource->retain('56wyNnSmuA6en32YnlLFoJNFLSI');

    expect($tx)->toBeInstanceOf(Transaction::class);
});

test('redact returns transaction', function (): void {
    $fixture = $this->loadFixture('transactions/void.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('put')
        ->once()
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/redact.json')
        ->andReturn($fixture);

    $resource = new PaymentMethodResource($transporter);
    $tx = $resource->redact('56wyNnSmuA6en32YnlLFoJNFLSI');

    expect($tx)->toBeInstanceOf(Transaction::class);
});

test('recache sends POST to recache endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/recache.json', ['payment_method' => ['verification_value' => '123']])
        ->andReturn($fixture);

    $resource = new PaymentMethodResource($transporter);
    $tx = $resource->recache('56wyNnSmuA6en32YnlLFoJNFLSI', ['verification_value' => '123']);

    expect($tx)->toBeInstanceOf(Transaction::class);
});

test('update sends PUT request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('payment_methods/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('put')
        ->once()
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI.json', ['payment_method' => ['first_name' => 'John']])
        ->andReturn($fixture);

    $resource = new PaymentMethodResource($transporter);
    $pm = $resource->update('56wyNnSmuA6en32YnlLFoJNFLSI', ['first_name' => 'John']);

    expect($pm)->toBeInstanceOf(PaymentMethod::class);
});

test('store sends POST to store endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('gateways/gw_token/store.json', ['transaction' => ['payment_method_token' => '56wyNnSmuA6en32YnlLFoJNFLSI']])
        ->andReturn($fixture);

    $resource = new PaymentMethodResource($transporter);
    $tx = $resource->store('gw_token', ['payment_method_token' => '56wyNnSmuA6en32YnlLFoJNFLSI']);

    expect($tx)->toBeInstanceOf(Transaction::class);
});

test('transactions returns paginated collection', function (): void {
    $txFixture = $this->loadFixture('transactions/purchase.json')['transaction'];

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/transactions.json', ['order' => 'desc'])
        ->andReturn(['transactions' => [$txFixture]]);

    $resource = new PaymentMethodResource($transporter);
    $collection = $resource->transactions('56wyNnSmuA6en32YnlLFoJNFLSI');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(1);
    expect($collection->items[0])->toBeInstanceOf(Transaction::class);
});

test('transactions passes since_token for pagination', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods/pm_token/transactions.json', ['order' => 'desc', 'since_token' => 'tok123'])
        ->andReturn(['transactions' => []]);

    $resource = new PaymentMethodResource($transporter);
    $collection = $resource->transactions('pm_token', 'tok123');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(0);
});

test('deleteMetadata sends DELETE request', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('delete')
        ->once()
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/metadata.json', [], [])
        ->andReturn([]);

    $resource = new PaymentMethodResource($transporter);
    $result = $resource->deleteMetadata('56wyNnSmuA6en32YnlLFoJNFLSI');

    expect($result)->toBe([]);
});

test('networkTokenizationMetadata sends GET request to correct endpoint', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('network_tokenization/card_metadata.json', ['payment_method_token' => '56wyNnSmuA6en32YnlLFoJNFLSI'])
        ->andReturn(['card_metadata' => ['backgroundColor' => '0x7aff54']]);

    $resource = new PaymentMethodResource($transporter);
    $result = $resource->networkTokenizationMetadata('56wyNnSmuA6en32YnlLFoJNFLSI');

    expect($result)->toBeArray();
    expect($result['card_metadata']['backgroundColor'])->toBe('0x7aff54');
});

test('networkTokenizationStatus sends GET request to correct endpoint', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('network_tokenization/token_status.json', ['payment_method_token' => '56wyNnSmuA6en32YnlLFoJNFLSI'])
        ->andReturn(['token_status' => 'ACTIVE']);

    $resource = new PaymentMethodResource($transporter);
    $result = $resource->networkTokenizationStatus('56wyNnSmuA6en32YnlLFoJNFLSI');

    expect($result)->toBeArray();
    expect($result['token_status'])->toBe('ACTIVE');
});

test('listEvents returns paginated collection of events', function (): void {
    $eventFixture = $this->loadFixture('payment_methods/event.json')['payment_method_event'];

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods/events.json', [])
        ->andReturn(['payment_method_events' => [$eventFixture]]);

    $resource = new PaymentMethodResource($transporter);
    $collection = $resource->listEvents();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(1);
    expect($collection->items[0])->toBeInstanceOf(PaymentMethodEvent::class);
    expect($collection->items[0]->token)->toBe('PME123abc456DEF789ghi');
});

test('listEvents passes since_token for pagination', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods/events.json', ['since_token' => 'tok123'])
        ->andReturn(['payment_method_events' => []]);

    $resource = new PaymentMethodResource($transporter);
    $collection = $resource->listEvents('tok123');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(0);
});

test('listEventsForPaymentMethod returns paginated collection', function (): void {
    $eventFixture = $this->loadFixture('payment_methods/event.json')['payment_method_event'];

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/events.json', [])
        ->andReturn(['payment_method_events' => [$eventFixture]]);

    $resource = new PaymentMethodResource($transporter);
    $collection = $resource->listEventsForPaymentMethod('56wyNnSmuA6en32YnlLFoJNFLSI');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(1);
    expect($collection->items[0])->toBeInstanceOf(PaymentMethodEvent::class);
});

test('retrieveEvent sends GET request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('payment_methods/event.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods/events/EVT123abc456DEF789ghi.json')
        ->andReturn($fixture);

    $resource = new PaymentMethodResource($transporter);
    $event = $resource->retrieveEvent('EVT123abc456DEF789ghi');

    expect($event)->toBeInstanceOf(PaymentMethodEvent::class);
    expect($event->token)->toBe('PME123abc456DEF789ghi');
});

test('updateGratis sends PUT request to update_gratis endpoint', function (): void {
    $fixture = $this->loadFixture('payment_methods/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('put')
        ->once()
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/update_gratis.json', ['payment_method' => ['first_name' => 'Jane']])
        ->andReturn($fixture);

    $resource = new PaymentMethodResource($transporter);
    $pm = $resource->updateGratis('56wyNnSmuA6en32YnlLFoJNFLSI', ['first_name' => 'Jane']);

    expect($pm)->toBeInstanceOf(PaymentMethod::class);
});

test('list forwards the state, metadata and count filters', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods.json', ['order' => 'desc', 'state' => 'retained', 'metadata' => 'plan:pro', 'count' => 100])
        ->andReturn(['payment_methods' => []]);

    $resource = new PaymentMethodResource($transporter);

    expect($resource->list(state: 'retained', metadata: 'plan:pro', count: 100)->count())->toBe(0);
});

test('listEvents forwards the event filters', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods/events.json', ['order' => 'asc', 'event_type' => 'card_updated', 'count' => 50, 'include_transactions' => true])
        ->andReturn(['events' => []]);

    $resource = new PaymentMethodResource($transporter);

    expect($resource->listEvents(order: 'asc', eventType: 'card_updated', count: 50, includeTransactions: true)->count())->toBe(0);
});

test('retain can ask for a network token to be provisioned', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('put')
        ->once()
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/retain.json', ['provision_network_token' => true])
        ->andReturn($fixture);

    $resource = new PaymentMethodResource($transporter);

    expect($resource->retain('56wyNnSmuA6en32YnlLFoJNFLSI', true))->toBeInstanceOf(Transaction::class);
});

test('deleteMetadata sends the keys to remove as a body', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('delete')
        ->once()
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/metadata.json', [], ['keys' => ['another_key', 'final_key']])
        ->andReturn([]);

    $resource = new PaymentMethodResource($transporter);

    expect($resource->deleteMetadata('56wyNnSmuA6en32YnlLFoJNFLSI', ['another_key', 'final_key']))->toBe([]);
});
