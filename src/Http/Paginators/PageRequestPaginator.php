<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use Closure;
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
    protected readonly int $originalPage;

    /**
     * @var  \Closure(\Saloon\Contracts\Response): bool;
     */
    protected readonly Closure $hasNextPage;

    /**
     * @param \Saloon\Contracts\Connector $connector
     * @param \Saloon\Contracts\Request $originalRequest
     * @param int<0, max>|null $limit
     * @param callable(\Saloon\Contracts\Response): bool $hasNextPage
     * @param int<0, max> $page
     */
    public function __construct(
        Connector $connector,
        Request $originalRequest,
        ?int $limit,
        callable $hasNextPage,
        protected int $page = 1,
    ) {
        parent::__construct($connector, $originalRequest, $limit);

        $this->originalPage = $page;
        $this->hasNextPage = $hasNextPage(...);
    }

    /**
     * @return int
     */
    public function page(): int
    {
        return $this->page;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        parent::rewind();

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
        if (\is_null($this->lastResponse)) {
            return true;
        }

        return ($this->hasNextPage)($this->lastResponse);
    }

    /**
     * @return ($this->async is true ? \GuzzleHttp\Promise\PromiseInterface : TResponse)
     */
    public function current(): Response|PromiseInterface
    {
        $request = clone $this->originalRequest;
        $request->query()->merge([
            'limit', $this->limit,
            'page', $this->page,
        ]);

        // TODO: async

        return $this->lastResponse = $this->connector->send($request);
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->page;
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
     *     original_page: int,
     *     page: int,
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
     *     page: int,
     * }
     */
    public function __serialize(): array
    {
        return [
            ...parent::__serialize(),
            'original_page' => $this->originalPage,
            'page' => $this->page,
        ];
    }

    /**
     * @param array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit: int|null,
     *     page: int,
     * } $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);

        $this->originalPage = $data['original_page'];
        $this->page = $data['page'];
    }
}
