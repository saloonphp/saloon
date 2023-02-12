<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use Saloon\Contracts\Request;
use Saloon\Contracts\Connector;

class OffsetRequestPaginator extends Paginator
{
    /**
     * @var int
     */
    protected readonly int $originalOffset;

    /**
     * @var string
     */
    protected string $offsetName = 'offset';

    /**
     * @var int
     */
    protected int $currentOffset = 0;

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
    public function totalEntriesInResponse(): int
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

        return (int) ceil($this->totalEntriesInResponse() / $this->limit());
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
            $this->getLimitKeyName() => $this->limit(),
            $this->getOffsetKeyName() => $this->currentOffset(),
        ]);
    }

    /**
     * @return bool
     */
    protected function hasMorePages(): bool
    {
        return $this->currentOffset() >= $this->totalEntriesInResponse();
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

    /**
     * @param string $offsetName
     *
     * @return $this
     */
    public function setOffsetKeyName(string $offsetName): static
    {
        $this->offsetName = $offsetName;

        return $this;
    }

    /**
     * @return string
     */
    public function getOffsetKeyName(): string
    {
        return $this->offsetName;
    }

    /**
     * @return int
     */
    public function currentOffset(): int
    {
        return $this->currentOffset;
    }
}
