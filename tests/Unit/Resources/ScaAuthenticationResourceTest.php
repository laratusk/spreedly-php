<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Transaction;
use Laratusk\Spreedly\Resources\ScaAuthenticationResource;

test('authenticate sends POST to sca_authentication endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('sca_authentication/authenticate.json', ['transaction' => ['payment_method_token' => 'pm_token', 'gateway_token' => 'gw_token']])
        ->andReturn($fixture);

    $resource = new ScaAuthenticationResource($transporter);
    $tx = $resource->authenticate(['payment_method_token' => 'pm_token', 'gateway_token' => 'gw_token']);

    expect($tx)->toBeInstanceOf(Transaction::class);
    expect($tx->succeeded)->toBeTrue();
});
