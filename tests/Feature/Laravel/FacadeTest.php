<?php

declare(strict_types=1);

use Laratusk\Spreedly\Laravel\Facades\Spreedly;
use Laratusk\Spreedly\Resources\CardRefresherResource;
use Laratusk\Spreedly\Resources\CertificateResource;
use Laratusk\Spreedly\Resources\ClaimResource;
use Laratusk\Spreedly\Resources\ComposerResource;
use Laratusk\Spreedly\Resources\EnvironmentResource;
use Laratusk\Spreedly\Resources\EventResource;
use Laratusk\Spreedly\Resources\GatewayResource;
use Laratusk\Spreedly\Resources\MerchantProfileResource;
use Laratusk\Spreedly\Resources\PaymentMethodResource;
use Laratusk\Spreedly\Resources\PaymentResource;
use Laratusk\Spreedly\Resources\ProtectionEventResource;
use Laratusk\Spreedly\Resources\ReceiverResource;
use Laratusk\Spreedly\Resources\ScaAuthenticationResource;
use Laratusk\Spreedly\Resources\SubMerchantResource;
use Laratusk\Spreedly\Resources\TransactionResource;

test('facade resolves gateways resource', function (): void {
    expect(Spreedly::gateways())->toBeInstanceOf(GatewayResource::class);
});

test('facade resolves payment methods resource', function (): void {
    expect(Spreedly::paymentMethods())->toBeInstanceOf(PaymentMethodResource::class);
});

test('facade resolves transactions resource', function (): void {
    expect(Spreedly::transactions())->toBeInstanceOf(TransactionResource::class);
});

test('facade resolves receivers resource', function (): void {
    expect(Spreedly::receivers())->toBeInstanceOf(ReceiverResource::class);
});

test('facade resolves certificates resource', function (): void {
    expect(Spreedly::certificates())->toBeInstanceOf(CertificateResource::class);
});

test('facade resolves environments resource', function (): void {
    expect(Spreedly::environments())->toBeInstanceOf(EnvironmentResource::class);
});

test('facade resolves events resource', function (): void {
    expect(Spreedly::events())->toBeInstanceOf(EventResource::class);
});

test('facade resolves merchant profiles resource', function (): void {
    expect(Spreedly::merchantProfiles())->toBeInstanceOf(MerchantProfileResource::class);
});

test('facade resolves composer resource', function (): void {
    expect(Spreedly::composer())->toBeInstanceOf(ComposerResource::class);
});

test('facade resolves sca authentication resource', function (): void {
    expect(Spreedly::scaAuthentication())->toBeInstanceOf(ScaAuthenticationResource::class);
});

test('facade resolves sub merchants resource', function (): void {
    expect(Spreedly::subMerchants())->toBeInstanceOf(SubMerchantResource::class);
});

test('facade resolves card refresher resource', function (): void {
    expect(Spreedly::cardRefresher())->toBeInstanceOf(CardRefresherResource::class);
});

test('facade resolves claim resource', function (): void {
    expect(Spreedly::claim())->toBeInstanceOf(ClaimResource::class);
});

test('facade resolves payments resource', function (): void {
    expect(Spreedly::payments())->toBeInstanceOf(PaymentResource::class);
});

test('facade resolves protection events resource', function (): void {
    expect(Spreedly::protectionEvents())->toBeInstanceOf(ProtectionEventResource::class);
});
