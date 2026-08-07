<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Certificate;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\Resources\CertificateResource;

test('create sends POST request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('certificates/create.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('certificates.json', ['certificate' => ['common_name' => 'apple.pay.example.com']])
        ->andReturn($fixture);

    $resource = new CertificateResource($transporter);
    $cert = $resource->create(['common_name' => 'apple.pay.example.com']);

    expect($cert)->toBeInstanceOf(Certificate::class);
    expect($cert->token)->toBe('Cert123xoVyB');
    expect($cert->commonName)->toBe('apple.pay.example.com');
    expect($cert->state)->toBe('created');
});

test('list returns paginated collection', function (): void {
    $fixture = ['certificates' => [$this->loadFixture('certificates/create.json')['certificate']]];

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('certificates.json', [])
        ->andReturn($fixture);

    $resource = new CertificateResource($transporter);
    $collection = $resource->list();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(1);
    expect($collection->items[0])->toBeInstanceOf(Certificate::class);
});

test('list passes since_token for pagination', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('certificates.json', ['since_token' => 'some_token'])
        ->andReturn(['certificates' => []]);

    $resource = new CertificateResource($transporter);
    $collection = $resource->list('some_token');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
});

test('update sends PUT request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('certificates/create.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('put')
        ->once()
        ->with('certificates/Cert123xoVyB.json', ['certificate' => ['common_name' => 'updated.example.com']])
        ->andReturn($fixture);

    $resource = new CertificateResource($transporter);
    $cert = $resource->update('Cert123xoVyB', ['common_name' => 'updated.example.com']);

    expect($cert)->toBeInstanceOf(Certificate::class);
});

test('generate sends POST to generate endpoint', function (): void {
    $fixture = $this->loadFixture('certificates/create.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('certificates/generate.json', ['certificate' => ['algorithm' => 'ec-prime256v1', 'cn' => 'MyApp ApplePay Production Certificate']])
        ->andReturn($fixture);

    $resource = new CertificateResource($transporter);
    $cert = $resource->generate(['algorithm' => 'ec-prime256v1', 'cn' => 'MyApp ApplePay Production Certificate']);

    expect($cert)->toBeInstanceOf(Certificate::class);
});

test('list forwards the order filter', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('certificates.json', ['order' => 'asc'])
        ->andReturn(['certificates' => []]);

    $resource = new CertificateResource($transporter);

    expect($resource->list(order: 'asc')->count())->toBe(0);
});
