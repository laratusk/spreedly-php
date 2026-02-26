<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
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
use Laratusk\Spreedly\SpreedlyClient;

test('can be instantiated with credentials', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);

    $client = new SpreedlyClient(
        environmentKey: 'env_key',
        accessSecret: 'secret',
        transporter: $transporter,
    );

    expect($client)->toBeInstanceOf(SpreedlyClient::class);
});

test('exposes all resource properties', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);

    $client = new SpreedlyClient(
        environmentKey: 'env_key',
        accessSecret: 'secret',
        transporter: $transporter,
    );

    expect($client->gateways)->toBeInstanceOf(GatewayResource::class);
    expect($client->paymentMethods)->toBeInstanceOf(PaymentMethodResource::class);
    expect($client->transactions)->toBeInstanceOf(TransactionResource::class);
    expect($client->receivers)->toBeInstanceOf(ReceiverResource::class);
    expect($client->certificates)->toBeInstanceOf(CertificateResource::class);
    expect($client->environments)->toBeInstanceOf(EnvironmentResource::class);
    expect($client->events)->toBeInstanceOf(EventResource::class);
    expect($client->merchantProfiles)->toBeInstanceOf(MerchantProfileResource::class);
    expect($client->composer)->toBeInstanceOf(ComposerResource::class);
    expect($client->scaAuthentication)->toBeInstanceOf(ScaAuthenticationResource::class);
    expect($client->subMerchants)->toBeInstanceOf(SubMerchantResource::class);
    expect($client->cardRefresher)->toBeInstanceOf(CardRefresherResource::class);
    expect($client->claim)->toBeInstanceOf(ClaimResource::class);
    expect($client->payments)->toBeInstanceOf(PaymentResource::class);
    expect($client->protectionEvents)->toBeInstanceOf(ProtectionEventResource::class);
});

test('exposes all resource methods for facade compatibility', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);

    $client = new SpreedlyClient(
        environmentKey: 'env_key',
        accessSecret: 'secret',
        transporter: $transporter,
    );

    expect($client->gateways())->toBeInstanceOf(GatewayResource::class);
    expect($client->paymentMethods())->toBeInstanceOf(PaymentMethodResource::class);
    expect($client->transactions())->toBeInstanceOf(TransactionResource::class);
    expect($client->receivers())->toBeInstanceOf(ReceiverResource::class);
    expect($client->certificates())->toBeInstanceOf(CertificateResource::class);
    expect($client->environments())->toBeInstanceOf(EnvironmentResource::class);
    expect($client->events())->toBeInstanceOf(EventResource::class);
    expect($client->merchantProfiles())->toBeInstanceOf(MerchantProfileResource::class);
    expect($client->composer())->toBeInstanceOf(ComposerResource::class);
    expect($client->scaAuthentication())->toBeInstanceOf(ScaAuthenticationResource::class);
    expect($client->subMerchants())->toBeInstanceOf(SubMerchantResource::class);
    expect($client->cardRefresher())->toBeInstanceOf(CardRefresherResource::class);
    expect($client->claim())->toBeInstanceOf(ClaimResource::class);
    expect($client->payments())->toBeInstanceOf(PaymentResource::class);
    expect($client->protectionEvents())->toBeInstanceOf(ProtectionEventResource::class);
});

test('accepts custom transporter', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);

    $client = new SpreedlyClient(
        environmentKey: 'env_key',
        accessSecret: 'secret',
        transporter: $transporter,
    );

    expect($client)->toBeInstanceOf(SpreedlyClient::class);
});
