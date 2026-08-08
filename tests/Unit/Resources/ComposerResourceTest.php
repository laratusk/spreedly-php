<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Transaction;
use Laratusk\Spreedly\Resources\ComposerResource;

test('authorize sends POST to composer authorize endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/authorize.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('transactions/authorize.json', ['transaction' => ['payment_method_token' => 'pm_token', 'amount' => 1000, 'currency_code' => 'USD']])
        ->andReturn($fixture);

    $resource = new ComposerResource($transporter);
    $tx = $resource->authorize(['payment_method_token' => 'pm_token', 'amount' => 1000, 'currency_code' => 'USD']);

    expect($tx)->toBeInstanceOf(Transaction::class);
    expect($tx->succeeded)->toBeTrue();
});

test('purchase sends POST to composer purchase endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('transactions/purchase.json', ['transaction' => ['payment_method_token' => 'pm_token', 'amount' => 1000, 'currency_code' => 'USD']])
        ->andReturn($fixture);

    $resource = new ComposerResource($transporter);
    $tx = $resource->purchase(['payment_method_token' => 'pm_token', 'amount' => 1000, 'currency_code' => 'USD']);

    expect($tx)->toBeInstanceOf(Transaction::class);
    expect($tx->amount)->toBe(1000);
});

test('verify sends POST to composer verify endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('transactions/verify.json', ['transaction' => ['payment_method_token' => 'pm_token']])
        ->andReturn($fixture);

    $resource = new ComposerResource($transporter);
    $tx = $resource->verify(['payment_method_token' => 'pm_token']);

    expect($tx)->toBeInstanceOf(Transaction::class);
});
