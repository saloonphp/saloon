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
     * @return int
     */
    public function totalPages(): int
    {
        // Make sure we have a response.
        if (is_null($this->currentResponse)) {
            $this->current();
        }

        return (int) ceil($this->totalEntries() / $this->limit());
    }

    protected function reset(): void
    {
        // Rewind to the original offset, instead of strictly the first offset.
        // Otherwise it could be an 'unexpected' behaviour, if we don't start over from where we started.
        $this->currentOffset = $this->originalOffset;
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
     * @return bool
     */
    protected function isFinished(): bool
    {
        return $this->currentOffset() >= $this->totalEntries();
    }

    /**
     * @return int
     */
    #[\ReturnTypeWillChange]
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
