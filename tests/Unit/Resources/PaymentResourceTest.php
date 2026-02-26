<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Payment;
use Laratusk\Spreedly\Resources\PaymentResource;

test('retrieve sends GET request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('payments/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('payments/PAY123abc456DEF789ghi.json')
        ->andReturn($fixture);

    $resource = new PaymentResource($transporter);
    $payment = $resource->retrieve('PAY123abc456DEF789ghi');

    expect($payment)->toBeInstanceOf(Payment::class);
    expect($payment->token)->toBe('PAY123abc456DEF789ghi');
    expect($payment->state)->toBe('succeeded');
    expect($payment->amount)->toBe(1500);
    expect($payment->currencyCode)->toBe('USD');
});
