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
class PageRequestPaginator extends RequestPaginator
{
    /**
     * @var int
     */
    protected readonly int $originalPage;

    /**
     * @param \Saloon\Contracts\Connector $connector
     * @param \Saloon\Contracts\Request $originalRequest
     * @param int<0, max>|null $limit
     * @param int<0, max> $page
     */
    public function __construct(
        Connector $connector,
        Request $originalRequest,
        ?int $limit,
        protected int $page = 1,
    ) {
        parent::__construct($connector, $originalRequest, $limit);

        $this->originalPage = $page;
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

        return $this->lastResponse->json('last_page');
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
     * @return int|null
     */
    public function previousPage(): ?int
    {
        return $this->currentPage() > $this->firstPage()
            ? $this->currentPage() - 1
            : null;
    }

    /**
     * @return bool
     */
    public function hasPreviousPage(): bool
    {
        return ! is_null($this->previousPage());
    }

    /**
     * @return int
     */
    public function currentPage(): int
    {
        return $this->page;
    }

    /**
     * @return int|null
     */
    public function nextPage(): ?int
    {
        return $this->currentPage() < $this->totalPages()
            ? $this->currentPage() + 1
            : null;
    }

    /**
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return ! is_null($this->nextPage());
    }

    /**
     * @return int
     */
    public function lastPage(): int
    {
        return $this->lastResponse->json('last_page');
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

        // TODO: Rewind completely, or rewind to originalPage?
        $this->page = 1;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        // If we haven't sent a request yet, and therefore don't have any response, the iterator is still valid.
        // It's only ever invalid when we have sent a request, retrieved the response, and have no more pages after that.
        // Otherwise PHP would immediately terminate  the iteration, without sending a request.
        if (is_null($this->lastResponse)) {
            return true;
        }

        return $this->hasNextPage();
    }

    /**
     * @return ($this->async is true ? \GuzzleHttp\Promise\PromiseInterface : TResponse)
     */
    public function current(): Response|PromiseInterface
    {
        $request = clone $this->originalRequest;

        if (! is_null($this->limit())) {
            $request->query()->add('limit', $this->limit());
        }

        $request->query()->add('page', $this->currentPage());

        // TODO: async

        return $this->lastResponse = $this->connector->send($request);
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->currentPage();
    }

    /**
     * @return void
     */
    public function next(): void
    {
        $this->page++;
    }

    /**
     * Go to the previous page.
     * Note that this method is *not* part of PHP's {@see \Iterator}, and will not work in loops, unless manually called.
     *
     * @return void
     */
    public function previous(): void
    {
        $this->page--;
    }

    /**
     * @return array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit: int|null,
     *     continue_on_new_loop: bool,
     *     original_page: int,
     *     current_page: int,
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
     *     original_page: int,
     *     continue_on_new_loop: bool,
     *     current_page: int,
     * }
     */
    public function __serialize(): array
    {
        return [
            ...parent::__serialize(),
            'original_page' => $this->originalPage,
            'current_page' => $this->page,
        ];
    }

    /**
     * @param array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit: int|null,
     *     continue_on_new_loop: bool,
     *     current_page: int,
     * } $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);

        $this->originalPage = $data['original_page'];
        $this->page = $data['current_page'];
    }
}
