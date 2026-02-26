<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\AccessSecret;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\Environment;
use Laratusk\Spreedly\Resources\EnvironmentResource;

test('retrieve sends GET request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('environments/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('environments/env_key_abc123.json')
        ->andReturn($fixture);

    $resource = new EnvironmentResource($transporter);
    $env = $resource->retrieve('env_key_abc123');

    expect($env)->toBeInstanceOf(Environment::class);
    expect($env->key)->toBe('env_key_abc123');
    expect($env->name)->toBe('My Environment');
    expect($env->test)->toBeTrue();
});

test('list returns paginated collection', function (): void {
    $fixture = ['environments' => [$this->loadFixture('environments/show.json')['environment']]];

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('environments.json', [])
        ->andReturn($fixture);

    $resource = new EnvironmentResource($transporter);
    $collection = $resource->list();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(1);
    expect($collection->items[0])->toBeInstanceOf(Environment::class);
});

test('create sends POST to environments endpoint', function (): void {
    $fixture = $this->loadFixture('environments/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('environments.json', ['environment' => ['name' => 'New Env']])
        ->andReturn($fixture);

    $resource = new EnvironmentResource($transporter);
    $env = $resource->create(['name' => 'New Env']);

    expect($env)->toBeInstanceOf(Environment::class);
});

test('update sends PUT to environment endpoint', function (): void {
    $fixture = $this->loadFixture('environments/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('put')
        ->once()
        ->with('environments/env_key_abc123.json', ['environment' => ['name' => 'Updated']])
        ->andReturn($fixture);

    $resource = new EnvironmentResource($transporter);
    $env = $resource->update('env_key_abc123', ['name' => 'Updated']);

    expect($env)->toBeInstanceOf(Environment::class);
});

test('regenerate signing secret sends POST to correct endpoint', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('environments/regenerate_signing_secret.json')
        ->andReturn(['signing_secret' => 'new_secret_abc123']);

    $resource = new EnvironmentResource($transporter);
    $result = $resource->regenerateSigningSecret();

    expect($result)->toBeArray();
    expect($result['signing_secret'])->toBe('new_secret_abc123');
});

test('createAccessSecret sends POST to correct endpoint', function (): void {
    $fixture = $this->loadFixture('environments/access_secret.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('environments/env_key_abc123/access_secrets.json', ['access_secret' => ['name' => 'My Secret']])
        ->andReturn($fixture);

    $resource = new EnvironmentResource($transporter);
    $secret = $resource->createAccessSecret('env_key_abc123', ['name' => 'My Secret']);

    expect($secret)->toBeInstanceOf(AccessSecret::class);
    expect($secret->token)->toBe('AS1234567890abcdefghi');
    expect($secret->name)->toBe('My Access Secret');
});

test('listAccessSecrets returns paginated collection', function (): void {
    $fixture = $this->loadFixture('environments/access_secrets_list.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('environments/env_key_abc123/access_secrets.json')
        ->andReturn($fixture);

    $resource = new EnvironmentResource($transporter);
    $collection = $resource->listAccessSecrets('env_key_abc123');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(2);
    expect($collection->items[0])->toBeInstanceOf(AccessSecret::class);
    expect($collection->items[0]->token)->toBe('AS1234567890abcdefghi');
    expect($collection->items[1]->token)->toBe('AS9876543210zyxwvutsr');
});

test('retrieveAccessSecret sends GET to correct endpoint', function (): void {
    $fixture = $this->loadFixture('environments/access_secret.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('environments/env_key_abc123/access_secrets/AS1234567890abcdefghi.json')
        ->andReturn($fixture);

    $resource = new EnvironmentResource($transporter);
    $secret = $resource->retrieveAccessSecret('env_key_abc123', 'AS1234567890abcdefghi');

    expect($secret)->toBeInstanceOf(AccessSecret::class);
    expect($secret->token)->toBe('AS1234567890abcdefghi');
    expect($secret->description)->toBe('Used for production integrations');
});

test('deleteAccessSecret sends DELETE to correct endpoint', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('delete')
        ->once()
        ->with('environments/env_key_abc123/access_secrets/AS1234567890abcdefghi.json')
        ->andReturn([]);

    $resource = new EnvironmentResource($transporter);
    $result = $resource->deleteAccessSecret('env_key_abc123', 'AS1234567890abcdefghi');

    expect($result)->toBe([]);
});
