<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\Receiver;
use Laratusk\Spreedly\DataTransferObjects\Transaction;
use Laratusk\Spreedly\Resources\ReceiverResource;

test('create sends POST request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('receivers/create.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('receivers.json', ['receiver' => ['receiver_type' => 'braintree']])
        ->andReturn($fixture);

    $resource = new ReceiverResource($transporter);
    $receiver = $resource->create(['receiver_type' => 'braintree']);

    expect($receiver)->toBeInstanceOf(Receiver::class);
    expect($receiver->token)->toBe('Rx1gI6fHgIuUkVnmUGPA3xoVyB');
    expect($receiver->receiverType)->toBe('braintree');
    expect($receiver->state)->toBe('retained');
});

test('retrieve sends GET request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('receivers/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('receivers/Rx1gI6fHgIuUkVnmUGPA3xoVyB.json')
        ->andReturn($fixture);

    $resource = new ReceiverResource($transporter);
    $receiver = $resource->retrieve('Rx1gI6fHgIuUkVnmUGPA3xoVyB');

    expect($receiver)->toBeInstanceOf(Receiver::class);
    expect($receiver->token)->toBe('Rx1gI6fHgIuUkVnmUGPA3xoVyB');
    expect($receiver->description)->toBe('My Braintree Receiver');
});

test('list returns paginated collection', function (): void {
    $fixture = $this->loadFixture('receivers/list.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('receivers.json', ['order' => 'desc'])
        ->andReturn($fixture);

    $resource = new ReceiverResource($transporter);
    $collection = $resource->list();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(1);
    expect($collection->items[0])->toBeInstanceOf(Receiver::class);
    expect($collection->items[0]->token)->toBe('Rx1gI6fHgIuUkVnmUGPA3xoVyB');
});

test('list passes since_token for pagination', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('receivers.json', ['order' => 'desc', 'since_token' => 'some_token'])
        ->andReturn(['receivers' => []]);

    $resource = new ReceiverResource($transporter);
    $collection = $resource->list('some_token');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
});

test('update sends PUT request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('receivers/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('put')
        ->once()
        ->with('receivers/Rx1gI6fHgIuUkVnmUGPA3xoVyB.json', ['receiver' => ['description' => 'Updated']])
        ->andReturn($fixture);

    $resource = new ReceiverResource($transporter);
    $receiver = $resource->update('Rx1gI6fHgIuUkVnmUGPA3xoVyB', ['description' => 'Updated']);

    expect($receiver)->toBeInstanceOf(Receiver::class);
});

test('redact sends PUT request to redact endpoint', function (): void {
    $fixture = $this->loadFixture('receivers/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('put')
        ->once()
        ->with('receivers/Rx1gI6fHgIuUkVnmUGPA3xoVyB/redact.json')
        ->andReturn($fixture);

    $resource = new ReceiverResource($transporter);
    $receiver = $resource->redact('Rx1gI6fHgIuUkVnmUGPA3xoVyB');

    expect($receiver)->toBeInstanceOf(Receiver::class);
});

test('deliver sends POST to deliver endpoint', function (): void {
    $fixture = $this->loadFixture('transactions/purchase.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('receivers/Rx1gI6fHgIuUkVnmUGPA3xoVyB/deliver.json', Mockery::any())
        ->andReturn($fixture);

    $resource = new ReceiverResource($transporter);
    $transaction = $resource->deliver('Rx1gI6fHgIuUkVnmUGPA3xoVyB', ['payment_method_token' => 'pm_token']);

    expect($transaction)->toBeInstanceOf(Transaction::class);
    expect($transaction->succeeded)->toBeTrue();
});

test('supportedReceivers sends GET to receivers_options endpoint', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('receivers_options.json')
        ->andReturn(['receiver_types' => []]);

    $resource = new ReceiverResource($transporter);
    $result = $resource->supportedReceivers();

    expect($result)->toBeArray();
});
