<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects\Collections;

use ArrayIterator;
use Closure;
use Countable;
use Generator;
use IteratorAggregate;
use Traversable;

/**
 * A paginated collection for Spreedly's token-based pagination.
 * Spreedly uses since_token (not page numbers) for pagination.
 *
 * @template T
 *
 * @implements IteratorAggregate<int, T>
 */
final readonly class PaginatedCollection implements Countable, IteratorAggregate
{
    /**
     * @param  array<T>  $items
     * @param  Closure(string): self<T>  $fetcher
     */
    public function __construct(
        public array $items,
        public ?string $sinceToken,
        public bool $hasMore,
        private Closure $fetcher,
    ) {}

    /**
     * Fetch the next page of results.
     *
     * @return self<T>|null
     */
    public function nextPage(): ?self
    {
        if (! $this->hasMore || $this->sinceToken === null) {
            return null;
        }

        return ($this->fetcher)($this->sinceToken);
    }

    /**
     * Auto-paginate through all pages lazily.
     *
     * @return Generator<int, T>
     */
    public function autoPaginate(): Generator
    {
        $page = $this;

        while (true) {
            foreach ($page->items as $item) {
                yield $item;
            }

            if (! $page->hasMore) {
                break;
            }

            $nextPage = $page->nextPage();
            if (! $nextPage instanceof \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection) {
                break;
            }

            $page = $nextPage;
        }
    }

    /**
     * @return ArrayIterator<int, T>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
