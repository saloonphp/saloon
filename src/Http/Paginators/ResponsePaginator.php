<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use Generator;
use ReturnTypeWillChange;
use Saloon\Contracts\Response;
use Saloon\Contracts\ResponsePaginator as ResponsePaginatorContract;
use Saloon\Contracts\SerialisableResponsePaginator;

/**
 * @template TValue
 *
 * @implements ResponsePaginatorContract<TValue>
 */
abstract class ResponsePaginator implements ResponsePaginatorContract
{
    protected int $index = 1;

    protected iterable $items;

    public function __construct(
        protected readonly Response $response,
    ) {}

    public function response(): Response
    {
        return $this->response;
    }

    /**
     * @return iterable<int, TValue>
     */
    abstract protected function getItems(): iterable;

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->items = $this->getItems();
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->index < count($this->items);
    }

    /**
     * @return TValue
     */
    #[ReturnTypeWillChange]
    public function current(): mixed
    {
        return $this->internalIterator->current();
    }

    /**
     * @return string|int
     */
    public function key(): string|int
    {
        return $this->internalIterator->key();
    }

    /**
     * @return void
     */
    public function next(): void
    {
        $this->internalIterator->next();
    }

    /**
     * @return void
     */
    public function previous(): void
    {
        $this->index--;
    }
}
