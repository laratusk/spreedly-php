<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\Event;
use Laratusk\Spreedly\DataTransferObjects\PaymentMethod;
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
    expect($pm->lastFourDigits)->toBe('4242');
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
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/retain.json')
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
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/store.json', ['gateway_token' => 'gw_token'])
        ->andReturn($fixture);

    $resource = new PaymentMethodResource($transporter);
    $tx = $resource->store('56wyNnSmuA6en32YnlLFoJNFLSI', ['gateway_token' => 'gw_token']);

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
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/metadata.json')
        ->andReturn([]);

    $resource = new PaymentMethodResource($transporter);
    $result = $resource->deleteMetadata('56wyNnSmuA6en32YnlLFoJNFLSI');

    expect($result)->toBe([]);
});

test('networkTokenizationMetadata sends GET request to correct endpoint', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/network_tokenization_metadata.json')
        ->andReturn(['network_tokenization_metadata' => ['network_token' => 'tok_123']]);

    $resource = new PaymentMethodResource($transporter);
    $result = $resource->networkTokenizationMetadata('56wyNnSmuA6en32YnlLFoJNFLSI');

    expect($result)->toBeArray();
    expect($result['network_tokenization_metadata']['network_token'])->toBe('tok_123');
});

test('networkTokenizationStatus sends GET request to correct endpoint', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/network_tokenization_status.json')
        ->andReturn(['network_tokenization_status' => ['status' => 'active']]);

    $resource = new PaymentMethodResource($transporter);
    $result = $resource->networkTokenizationStatus('56wyNnSmuA6en32YnlLFoJNFLSI');

    expect($result)->toBeArray();
    expect($result['network_tokenization_status']['status'])->toBe('active');
});

test('listEvents returns paginated collection of events', function (): void {
    $eventFixture = $this->loadFixture('events/show.json')['event'];

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods/events.json', [])
        ->andReturn(['events' => [$eventFixture]]);

    $resource = new PaymentMethodResource($transporter);
    $collection = $resource->listEvents();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(1);
    expect($collection->items[0])->toBeInstanceOf(Event::class);
});

test('listEvents passes since_token for pagination', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods/events.json', ['since_token' => 'tok123'])
        ->andReturn(['events' => []]);

    $resource = new PaymentMethodResource($transporter);
    $collection = $resource->listEvents('tok123');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(0);
});

test('listEventsForPaymentMethod returns paginated collection', function (): void {
    $eventFixture = $this->loadFixture('events/show.json')['event'];

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods/56wyNnSmuA6en32YnlLFoJNFLSI/events.json', [])
        ->andReturn(['events' => [$eventFixture]]);

    $resource = new PaymentMethodResource($transporter);
    $collection = $resource->listEventsForPaymentMethod('56wyNnSmuA6en32YnlLFoJNFLSI');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(1);
    expect($collection->items[0])->toBeInstanceOf(Event::class);
});

test('retrieveEvent sends GET request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('events/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payment_methods/events/EVT123abc456DEF789ghi.json')
        ->andReturn($fixture);

    $resource = new PaymentMethodResource($transporter);
    $event = $resource->retrieveEvent('EVT123abc456DEF789ghi');

    expect($event)->toBeInstanceOf(Event::class);
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
