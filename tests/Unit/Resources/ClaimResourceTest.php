<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\Resources\ClaimResource;

test('create sends POST request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('claim/create.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('claim.json', ['payment_method_token' => 'PM1234567890abcdefgh'])
        ->andReturn($fixture);

    $resource = new ClaimResource($transporter);
    $result = $resource->create(['payment_method_token' => 'PM1234567890abcdefgh']);

    expect($result)->toBeArray();
    expect($result['claim']['token'])->toBe('CLM123abc456DEF789ghi');
    expect($result['claim']['state'])->toBe('succeeded');
});
