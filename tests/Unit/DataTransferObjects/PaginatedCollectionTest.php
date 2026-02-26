<?php

declare(strict_types=1);

use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;

test('can iterate over items', function (): void {
    $items = ['item1', 'item2', 'item3'];
    $collection = new PaginatedCollection(
        items: $items,
        sinceToken: null,
        hasMore: false,
        fetcher: fn (string $token): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => new PaginatedCollection([], null, false, fn (): null => null),
    );

    $collected = [];
    foreach ($collection as $item) {
        $collected[] = $item;
    }

    expect($collected)->toBe($items);
});

test('count returns correct number of items', function (): void {
    $collection = new PaginatedCollection(
        items: ['a', 'b', 'c'],
        sinceToken: 'token_c',
        hasMore: false,
        fetcher: fn (string $token): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => new PaginatedCollection([], null, false, fn (): null => null),
    );

    expect($collection->count())->toBe(3);
    expect(count($collection))->toBe(3);
});

test('next page returns null when has no more', function (): void {
    $collection = new PaginatedCollection(
        items: ['a'],
        sinceToken: 'token',
        hasMore: false,
        fetcher: fn (string $token): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => new PaginatedCollection([], null, false, fn (): null => null),
    );

    expect($collection->nextPage())->toBeNull();
});

test('next page returns null when since token is null', function (): void {
    $collection = new PaginatedCollection(
        items: [],
        sinceToken: null,
        hasMore: true,
        fetcher: fn (string $token): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => new PaginatedCollection([], null, false, fn (): null => null),
    );

    expect($collection->nextPage())->toBeNull();
});

test('next page calls fetcher with since token', function (): void {
    $nextPageItems = ['d', 'e'];
    $fetcherCalled = false;
    $fetcherToken = null;

    $collection = new PaginatedCollection(
        items: ['a', 'b', 'c'],
        sinceToken: 'token_c',
        hasMore: true,
        fetcher: function (string $token) use (&$fetcherCalled, &$fetcherToken, $nextPageItems): PaginatedCollection {
            $fetcherCalled = true;
            $fetcherToken = $token;

            return new PaginatedCollection($nextPageItems, null, false, fn (): null => null);
        },
    );

    $nextPage = $collection->nextPage();

    expect($fetcherCalled)->toBeTrue();
    expect($fetcherToken)->toBe('token_c');
    expect($nextPage)->toBeInstanceOf(PaginatedCollection::class);
    expect($nextPage->items)->toBe($nextPageItems);
});

test('auto paginate yields all items across pages', function (): void {
    $page2 = new PaginatedCollection(
        items: ['d', 'e'],
        sinceToken: null,
        hasMore: false,
        fetcher: fn (string $token): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => new PaginatedCollection([], null, false, fn (): null => null),
    );

    $page1 = new PaginatedCollection(
        items: ['a', 'b', 'c'],
        sinceToken: 'token_c',
        hasMore: true,
        fetcher: fn (string $token): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => $page2,
    );

    $allItems = iterator_to_array($page1->autoPaginate(), false);

    expect($allItems)->toBe(['a', 'b', 'c', 'd', 'e']);
});

test('auto paginate works with single page', function (): void {
    $collection = new PaginatedCollection(
        items: ['x', 'y'],
        sinceToken: null,
        hasMore: false,
        fetcher: fn (string $token): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => new PaginatedCollection([], null, false, fn (): null => null),
    );

    $allItems = iterator_to_array($collection->autoPaginate(), false);

    expect($allItems)->toBe(['x', 'y']);
});
