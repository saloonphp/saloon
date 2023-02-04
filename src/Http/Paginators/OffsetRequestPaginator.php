<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\Connector;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;

// TODO 1: Look into serialising the Connector and original Request,
//           to ensure that we can rebuild the paginator state without storing the entire multiverse.
// TODO 2: Make it easier to extend the RequestPaginator. Preferably via callbacks, so we ideally don't even need separate classes.
// TODO 3: Because both page-based and offset-based pagination just bumps the 'paging' number,
//           would it make sense to do all pagination in _the_ RequestPaginator, but allow to specify the counter somehow?
//         Maybe a callback that receives a copy of the original Request, as well as the latest Response (which has the corresponding latest Request and PendingRequest),
//           and have that callback set the next 'page' on the new Request?

/**
 * @template TRequest of \Saloon\Contracts\Request
 * @template TResponse of \Saloon\Contracts\Response
 *
 * @extends RequestPaginator<TRequest, TResponse>
 */
class OffsetRequestPaginator extends RequestPaginator
{
    /**
     * @var int
     */
    protected readonly int $originalOffset;

    /**
     * @param \Saloon\Contracts\Connector $connector
     * @param \Saloon\Contracts\Request $originalRequest
     * @param int<0, max> $limit
     * @param int<0, max> $offset
     */
    public function __construct(
        Connector $connector,
        Request $originalRequest,
        int $limit,
        protected int $offset = 0,
    ) {
        parent::__construct($connector, $originalRequest, $limit);

        $this->originalOffset = $offset;
    }

    /**
     * @return int
     */
    public function limit(): int
    {
        return parent::limit();
    }

    /**
     * @return int
     */
    public function totalPages(): int
    {
        // Make sure we have a response.
        if (is_null($this->lastResponse)) {
            $this->current();
        }

        return (int) ceil($this->totalEntries() / $this->limit());
    }

    /**
     * @return int
     */
    public function totalEntries(): int
    {
        // Make sure we have a response.
        if (is_null($this->lastResponse)) {
            $this->current();
        }

        return $this->lastResponse->json('total');
    }

    /**
     * @return int
     */
    public function firstPage(): int
    {
        // TODO: Or should we use something from the response?
        return 1;
    }

    /**
     * @return int
     */
    public function firstOffset(): int
    {
        // TODO: Or should we use something from the response?
        return 0;
    }

    /**
     * @return int|null
     */
    public function previousPage(): ?int
    {
        $page = $this->currentPage();

        return $page > $this->firstPage()
            ? $page - 1
            : null;
    }

    /**
     * @return int|null
     */
    public function previousOffset(): ?int
    {
        $previousOffset = $this->currentOffset() - $this->limit();

        return $previousOffset > $this->firstOffset()
            ? $previousOffset
            : null;
    }

    /**
     * @return int
     */
    public function currentPage(): int
    {
        return (int) ceil($this->totalEntries() / $this->totalPages());
    }

    /**
     * @return int
     */
    public function currentOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return int|null
     */
    public function nextPage(): ?int
    {
        $page = $this->currentPage();

        return $page < $this->lastPage()
            ? $page + 1
            : null;
    }

    /**
     * @return int|null
     */
    public function nextOffset(): ?int
    {
        $nextOffset = $this->currentOffset() + $this->limit();

        return $nextOffset < $this->lastOffset()
            ? $nextOffset
            : null;
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
        return (int) floor($this->totalEntries() / $this->totalPages());
    }

    /**
     * @return int
     */
    public function lastOffset(): int
    {
        return $this->totalEntries() - $this->limit();
    }

    /**
     * @return iterable<int, array<string, mixed>>
     *
     * @TODO entry data type
     */
    public function entries(): iterable
    {
        return $this->lastResponse->json('data');
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        parent::rewind();

        // TODO: Rewind completely, or rewind to originalOffset?
        $this->offset = 0;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        // If we haven't sent a request yet, and therefore don't have any response, the iterator is still valid.
        // It's only ever invalid when we have sent a request, retrieved the response, and have no more pages after that.
        // Otherwise PHP would immediately terminate  the iteration, without sending a request.
        if (\is_null($this->lastResponse)) {
            return true;
        }

        return $this->hasNextOffset();
    }

    /**
     * @return ($this->async is true ? \GuzzleHttp\Promise\PromiseInterface : TResponse)
     */
    public function current(): Response|PromiseInterface
    {
        $request = clone $this->originalRequest;
        $request->query()->merge([
            'limit', $this->limit(),
            'offset', $this->currentOffset(),
        ]);

        // TODO: async

        return $this->lastResponse = $this->connector->send($request);
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
        $this->offset += $this->limit;
    }

    /**
     * Go to the previous 'page'/offset.
     * Note that this method is *not* part of PHP's {@see \Iterator}, and will not work in loops, unless manually called.
     *
     * @return void
     */
    public function previous(): void
    {
        $this->offset -= $this->limit;
    }

    /**
     * @return array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit: int|null,
     *     original_offset: int,
     *     offset: int,
     * }
     *
     * @see \Saloon\Http\Paginators\PageRequestPaginator::__serialize()
     */
    public function jsonSerialize(): array
    {
        return $this->__serialize();
    }

    /**
     * @return array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit: int|null,
     *     original_offset: int,
     *     offset: int,
     * }
     */
    public function __serialize(): array
    {
        return [
            ...parent::__serialize(),
            'original_offset' => $this->originalOffset,
            'offset' => $this->offset,
        ];
    }

    /**
     * @param array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit: int|null,
     *     original_offset: int,
     *     offset: int,
     * } $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);

        $this->originalOffset = $data['original_offset'];
        $this->offset = $data['offset'];
    }
}
