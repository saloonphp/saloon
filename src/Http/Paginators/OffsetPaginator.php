<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use ReturnTypeWillChange;
use Saloon\Contracts\Request;
use Saloon\Contracts\Connector;

class OffsetPaginator extends Paginator
{
    /**
     * The original offset the paginator started from
     *
     * @var int
     */
    protected readonly int $originalOffset;

    /**
     * The query parameter key name for offset
     *
     * @var string
     */
    protected string $offsetKeyName = 'offset';

    /**
     * The current offset the iterator is on
     *
     * @var int
     */
    protected int $currentOffset = 0;

    /**
     * Constructor
     *
     * @param \Saloon\Contracts\Connector $connector
     * @param \Saloon\Contracts\Request $originalRequest
     * @param int $limit
     * @param int $offset
     */
    public function __construct(
        Connector $connector,
        Request $originalRequest,
        int $limit,
        int $offset = 0,
    ) {
        parent::__construct($connector, $originalRequest, $limit);

        $this->currentOffset = $this->originalOffset = $offset;
    }

    /**
     * Reset the iterator
     *
     * @return void
     */
    protected function reset(): void
    {
        // We'll rewind to the original offset instead strictly the first page. This is so
        // we start from when the user requested us to start from, not actually the beginning.

        $this->currentOffset = $this->originalOffset;
    }

    /**
     * Apply the pagination to the current request instance
     *
     * @param \Saloon\Contracts\Request $request
     * @return void
     */
    protected function applyPagination(Request $request): void
    {
        $request->query()->merge([
            $this->getLimitKeyName() => $this->limit(),
            $this->getOffsetKeyName() => $this->currentOffset(),
        ]);
    }

    /**
     * Check if the paginator has finished
     *
     * @return bool
     * @throws \Saloon\Exceptions\PaginatorException
     */
    protected function isFinished(): bool
    {
        return $this->currentOffset() >= $this->totalResults();
    }

    /**
     * Get the current key of the iterator
     *
     * @return int
     */
    #[ReturnTypeWillChange]
    public function key(): int
    {
        return $this->currentOffset();
    }

    /**
     * Move the iterator to the next item
     *
     * @return void
     */
    public function next(): void
    {
        $this->currentOffset += $this->limit();
    }

    /**
     * Set the offset query parameter key name
     *
     * @param string $offsetKeyName
     * @return $this
     */
    public function setOffsetKeyName(string $offsetKeyName): static
    {
        $this->offsetKeyName = $offsetKeyName;

        return $this;
    }

    /**
     * Get the offset key name
     *
     * @return string
     */
    public function getOffsetKeyName(): string
    {
        return $this->offsetKeyName;
    }

    /**
     * Get the current offset
     *
     * @return int
     */
    public function currentOffset(): int
    {
        return $this->currentOffset;
    }
}
