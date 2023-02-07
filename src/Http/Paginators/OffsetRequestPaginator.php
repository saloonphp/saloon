<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use Saloon\Contracts\Connector;
use Saloon\Contracts\Request;
use Saloon\Traits\Request\HasOffsetPagination;

// TODO 1: Look into serialising the Connector and original Request,
//           to ensure that we can rebuild the paginator state without storing the entire multiverse.

class OffsetRequestPaginator extends RequestPaginator
{
    use HasOffsetPagination;

    /**
     * @var int
     */
    protected readonly int $originalOffset;

    /**
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
     * @return int
     */
    public function totalEntries(): int
    {
        // Make sure we have a response.
        if (is_null($this->currentResponse)) {
            $this->current();
        }

        return $this->currentResponse->json('total');
    }

    /**
     * @return iterable<int, array<string, mixed>>
     *
     * @TODO entry data type
     */
    public function entries(): iterable
    {
        return $this->currentResponse->json('data');
    }

    /**
     * @return int
     */
    public function totalPages(): int
    {
        // Make sure we have a response.
        if (is_null($this->currentResponse)) {
            $this->current();
        }

        return $this->lastPage();
    }

    /**
     * @return int
     */
    public function firstPage(): int
    {
        return 1;
    }

    /**
     * @return int
     */
    public function firstOffset(): int
    {
        return 0;
    }

    /**
     * @return int|null
     */
    public function previousPage(): ?int
    {
        return $this->currentPage() > $this->firstPage() ? $this->currentPage() - 1  : null;
    }

    /**
     * @return int|null
     */
    public function previousOffset(): ?int
    {
        $previousOffset = $this->currentOffset() - $this->limit();

        return $previousOffset > $this->firstOffset() ? $previousOffset : null;
    }

    /**
     * @return bool
     */
    public function hasPreviousOffset(): bool
    {
        return ! is_null($this->previousOffset());
    }

    /**
     * @return int
     */
    public function currentPage(): int
    {
        return (int) ceil($this->totalEntries() / $this->totalPages());
    }

    /**
     * @return int|null
     */
    public function nextPage(): ?int
    {
        return $this->currentPage() < $this->lastPage() ? $this->currentPage() + 1 : null;
    }

    /**
     * @return int|null
     */
    public function nextOffset(): ?int
    {
        $nextOffset = $this->currentOffset() + $this->limit();

        return $nextOffset < $this->lastOffset() ? $nextOffset : null;
    }

    /**
     * @return bool
     */
    public function hasNextOffset(): bool
    {
        return ! is_null($this->nextOffset());
    }

    /**
     * @return int
     */
    public function lastPage(): int
    {
        // Make sure we have a response.
        if (is_null($this->currentResponse)) {
            $this->current();
        }

        return (int) floor($this->totalEntries() / $this->totalPages());
    }

    /**
     * @return int
     */
    public function lastOffset(): int
    {
        // Make sure we have a response.
        if (is_null($this->currentResponse)) {
            $this->current();
        }

        return $this->totalEntries() - $this->limit();
    }

    /**
     * @param \Saloon\Contracts\Request $request
     *
     * @return void
     */
    protected function applyPagination(Request $request): void
    {
        $request->query()->merge([
            $this->limitName() => $this->limit(),
            $this->offsetName() => $this->currentOffset(),
        ]);
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        if (! $this->shouldRewind()) {
            return;
        }

        parent::rewind();

        // Rewind to the original offset, instead of strictly the first offset.
        // Otherwise it could be an 'unexpected' behaviour, if we don't start over from where we started.
        $this->currentOffset = $this->originalOffset;
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->currentOffset();
    }

    /**
     * @return void
     */
    public function next(): void
    {
        $this->currentOffset += $this->limit();
    }
}
