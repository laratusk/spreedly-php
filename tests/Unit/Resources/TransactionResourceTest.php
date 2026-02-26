<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\Transaction;
use Laratusk\Spreedly\Resources\TransactionResource;

test('purchase sends POST to gateway purchase endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with(
            'gateways/6DqX57I6fHgIuUkVnmUGPA3xoVyB/purchase.json',
            [
                'transaction' => [
                    'payment_method_token' => '56wyNnSmuA6en32YnlLFoJNFLSI',
                    'amount' => 1000,
                    'currency_code' => 'USD',
                ],
            ],
        )
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->purchase('6DqX57I6fHgIuUkVnmUGPA3xoVyB', [
        'payment_method_token' => '56wyNnSmuA6en32YnlLFoJNFLSI',
        'amount' => 1000,
        'currency_code' => 'USD',
    ]);

    expect($tx)->toBeInstanceOf(Transaction::class);
    expect($tx->succeeded)->toBeTrue();
    expect($tx->amount)->toBe(1000);
    expect($tx->currencyCode)->toBe('USD');
    expect($tx->transactionType)->toBe('Purchase');
});

test('authorize sends POST to gateway authorize endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/authorize.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('gateways/gw_token/authorize.json', Mockery::any())
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->authorize('gw_token', [
        'payment_method_token' => 'pm_token',
        'amount' => 1000,
        'currency_code' => 'USD',
    ]);

    expect($tx)->toBeInstanceOf(Transaction::class);
    expect($tx->succeeded)->toBeTrue();
});

test('capture sends POST to transaction capture endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/capture.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('transactions/Dq1pzm6oIxPBbKVFxdBp2Vy0yGb/capture.json', Mockery::any())
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->capture('Dq1pzm6oIxPBbKVFxdBp2Vy0yGb', ['amount' => 1000]);

    expect($tx)->toBeInstanceOf(Transaction::class);
    expect($tx->transactionType)->toBe('Capture');
});

test('void sends POST to transaction void endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/void.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('transactions/Cq0pzm6oIxPBbKVFxdBp2Vy0yGa/void.json', [])
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->void('Cq0pzm6oIxPBbKVFxdBp2Vy0yGa');

    expect($tx)->toBeInstanceOf(Transaction::class);
    expect($tx->transactionType)->toBe('Void');
});

test('credit sends POST to transaction credit endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/credit.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('transactions/Cq0pzm6oIxPBbKVFxdBp2Vy0yGa/credit.json', ['transaction' => ['amount' => 500]])
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->credit('Cq0pzm6oIxPBbKVFxdBp2Vy0yGa', ['amount' => 500]);

    expect($tx)->toBeInstanceOf(Transaction::class);
    expect($tx->transactionType)->toBe('Credit');
    expect($tx->amount)->toBe(500);
});

test('retrieve sends GET to transaction endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('transactions/Cq0pzm6oIxPBbKVFxdBp2Vy0yGa.json')
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->retrieve('Cq0pzm6oIxPBbKVFxdBp2Vy0yGa');

    expect($tx)->toBeInstanceOf(Transaction::class);
    expect($tx->token)->toBe('Cq0pzm6oIxPBbKVFxdBp2Vy0yGa');
});

test('list returns paginated collection', function (): void {
    $fixture = ['transactions' => [$this->loadFixture('transactions/purchase.json')['transaction']]];

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('transactions.json', ['order' => 'desc'])
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $collection = $resource->list();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(1);
    expect($collection->items[0])->toBeInstanceOf(Transaction::class);
});

test('transcript sends GET raw request', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('getRaw')
        ->once()
        ->with('transactions/Cq0pzm6oIxPBbKVFxdBp2Vy0yGa/transcript.json')
        ->andReturn('GET /checkout HTTP/1.1...');

    $resource = new TransactionResource($transporter);
    $transcript = $resource->transcript('Cq0pzm6oIxPBbKVFxdBp2Vy0yGa');

    expect($transcript)->toBe('GET /checkout HTTP/1.1...');
});

test('transaction maps payment method nested dto', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('transactions/Cq0pzm6oIxPBbKVFxdBp2Vy0yGa.json')
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->retrieve('Cq0pzm6oIxPBbKVFxdBp2Vy0yGa');

    expect($tx->paymentMethod)->not->toBeNull();
    expect($tx->paymentMethod?->token)->toBe('56wyNnSmuA6en32YnlLFoJNFLSI');
    expect($tx->paymentMethod?->lastFourDigits)->toBe('4242');
});

test('generalCredit sends POST to general credit endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('gateways/gw_token/general_credit.json', ['transaction' => ['payment_method_token' => 'pm_token', 'amount' => 500, 'currency_code' => 'USD']])
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->generalCredit('gw_token', ['payment_method_token' => 'pm_token', 'amount' => 500, 'currency_code' => 'USD']);

    expect($tx)->toBeInstanceOf(Transaction::class);
    expect($tx->succeeded)->toBeTrue();
});

test('verify sends POST to verify endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/authorize.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('gateways/gw_token/verify.json', ['transaction' => ['payment_method_token' => 'pm_token']])
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->verify('gw_token', ['payment_method_token' => 'pm_token']);

    expect($tx)->toBeInstanceOf(Transaction::class);
    expect($tx->succeeded)->toBeTrue();
});

test('update sends PATCH to transaction endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('patch')
        ->once()
        ->with('transactions/Cq0pzm6oIxPBbKVFxdBp2Vy0yGa.json', ['transaction' => ['order_id' => 'order_123']])
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->update('Cq0pzm6oIxPBbKVFxdBp2Vy0yGa', ['order_id' => 'order_123']);

    expect($tx)->toBeInstanceOf(Transaction::class);
});

test('complete sends POST to complete endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('transactions/Cq0pzm6oIxPBbKVFxdBp2Vy0yGa/complete.json', [])
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->complete('Cq0pzm6oIxPBbKVFxdBp2Vy0yGa');

    expect($tx)->toBeInstanceOf(Transaction::class);
});

test('complete sends POST with params', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('transactions/Cq0pzm6oIxPBbKVFxdBp2Vy0yGa/complete.json', ['transaction' => ['pares' => 'abc123']])
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->complete('Cq0pzm6oIxPBbKVFxdBp2Vy0yGa', ['pares' => 'abc123']);

    expect($tx)->toBeInstanceOf(Transaction::class);
});

test('confirm sends POST to confirm endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('transactions/Cq0pzm6oIxPBbKVFxdBp2Vy0yGa/confirm.json', [])
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->confirm('Cq0pzm6oIxPBbKVFxdBp2Vy0yGa');

    expect($tx)->toBeInstanceOf(Transaction::class);
});

test('confirm sends POST with params', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('transactions/tx_token/confirm.json', ['transaction' => ['checkout_id' => 'chk_123']])
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->confirm('tx_token', ['checkout_id' => 'chk_123']);

    expect($tx)->toBeInstanceOf(Transaction::class);
});

test('referencePurchase sends POST to gateway purchase endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('gateways/gw_token/purchase.json', ['transaction' => ['reference_token' => 'ref_tok', 'amount' => 1000, 'currency_code' => 'USD']])
        ->andReturn($fixture);

    $resource = new TransactionResource($transporter);
    $tx = $resource->referencePurchase('gw_token', ['reference_token' => 'ref_tok', 'amount' => 1000, 'currency_code' => 'USD']);

    expect($tx)->toBeInstanceOf(Transaction::class);
    expect($tx->succeeded)->toBeTrue();
});

test('list passes since_token for pagination', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('transactions.json', ['order' => 'desc', 'since_token' => 'tok456'])
        ->andReturn(['transactions' => []]);

    $resource = new TransactionResource($transporter);
    $collection = $resource->list('tok456');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(0);
});
