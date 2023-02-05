<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\Connector;
use Saloon\Contracts\Pool;
use Saloon\Contracts\Request;
use Saloon\Contracts\RequestPaginator as RequestPaginatorContract;
use Saloon\Contracts\Response;
use Saloon\Contracts\SerialisableRequestPaginator;

use function is_null;

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
 * @implements RequestPaginatorContract<TRequest, TResponse>
 */
abstract class RequestPaginator implements RequestPaginatorContract, SerialisableRequestPaginator
{
    /**
     * @var bool
     */
    protected bool $async = false;

    /**
     * Whether or not the Paginator will skip rewinding, if it's used in a new loop.
     * If not, it'll continue where it left off in a previous loop.
     * While iterators, by design, starts over, Request Paginators should continue.
     * Otherwise it'll re-send earlier requests.
     * So make it default not to rewind, unless explicitly told to do so.
     *
     * @var bool
     *
     * @see \Saloon\Contracts\RequestPaginator::ignoreRewinding()
     *
     * @TODO Come up with a better name for this method.
     */
    protected bool $shouldRewind = false;

    /**
     * @var TResponse|null
     */
    protected ?Response $currentResponse = null;

    /**
     * @param \Saloon\Contracts\Connector $connector
     * @param TRequest $originalRequest
     * @param int|null $limit
     */
    public function __construct(
        protected readonly Connector $connector,
        protected readonly Request $originalRequest,
        protected ?int $limit = null,
    ) {}

    /**
     * Apply paging information, like setting the Request query parameter 'page', or 'offset', etc.
     *
     * @param \Saloon\Contracts\Request $request
     *
     * @return void
     */
    abstract protected function applyPaging(Request $request): void;

    /**
     * @param callable(int $pendingRequests): (int)|int $concurrency
     * @param callable(TResponse $response, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $responseHandler
     * @param callable(mixed $reason, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $exceptionHandler
     * @return \Saloon\Contracts\Pool
     */
    public function pool(
        callable|int $concurrency = 5,
        ?callable $responseHandler = null,
        ?callable $exceptionHandler = null,
    ): Pool {
        // The purpose of a pool is to concurrently send requests.
        // So 'force' set async.
        $this->async();

        return $this->connector->pool(
            $this,
            $concurrency,
            $responseHandler,
            $exceptionHandler,
        );
    }

    /**
     * @param bool $async
     *
     * @return $this
     */
    public function async(bool $async = true): static
    {
        $this->async = $async;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAsync(): bool
    {
        return $this->async;
    }

    /**
     * @param bool $ignoreRewinding
     *
     * @return $this
     */
    public function ignoreRewinding(bool $ignoreRewinding = true): static
    {
        $this->shouldRewind = ! $ignoreRewinding;

        return $this;
    }

    /**
     * @return bool
     */
    public function shouldRewind(): bool
    {
        return $this->shouldRewind;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->totalPages();
    }

    /**
     * @return \Saloon\Contracts\Response|null
     */
    public function currentResponse(): ?Response
    {
        return $this->currentResponse;
    }

    /**
     * @return int|null
     */
    public function limit(): ?int
    {
        return $this->limit;
    }

    /**
     * @return bool
     *
     * @TODO: Make naming more abstract and versatile.
     *        F.e., page, offset
     */
    public function hasPreviousPage(): bool
    {
        return ! is_null($this->previousPage());
    }

    /**
     * @return bool
     *
     * @TODO: Make naming more abstract and versatile.
     *        F.e., page, offset
     */
    public function hasNextPage(): bool
    {
        return ! is_null($this->nextPage());
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        if (! $this->shouldRewind()) {
            return;
        }

        // Nullify the current response, so we don't accidentally check if there are more pages, even though we're starting over.
        $this->currentResponse = null;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        // If we haven't sent a request yet, and therefore don't have any response, the iterator is still valid.
        // It's only ever invalid when we have sent a request, retrieved the response, and have no more pages after that.
        // Otherwise PHP would immediately terminate  the iteration, without sending a request.
        if (is_null($this->currentResponse)) {
            return true;
        }

        return $this->hasNextPage();
    }

    /**
     * @return ($this->async is true ? \GuzzleHttp\Promise\PromiseInterface : TResponse)
     */
    public function current(): Response|PromiseInterface
    {
        $this->applyPaging(
            $request = clone $this->originalRequest,
        );

        // TODO: async

        return $this->currentResponse = $this->connector->send($request);
    }

    /**
     * @return array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit: int|null,
     *     should_rewind: bool,
     * }
     *
     * @see \Saloon\Http\Paginators\RequestPaginator::__serialize()
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
     *     should_rewind: bool,
     * }
     */
    public function __serialize(): array
    {
        // TODO: figure out how to serialise the 'has next page' resolver/callback.

        return [
            'connector' => $this->connector,
            'original_request' => $this->originalRequest,
            'limit' => $this->limit,
            'should_rewind' => $this->shouldRewind,
        ];
    }

    /**
     * @param array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit: int|null,
     *     should_rewind: bool,
     * } $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->connector = $data['connector'];
        $this->originalRequest = $data['original_request'];
        $this->limit = $data['limit'];
        $this->shouldRewind = $data['should_rewind'];
    }
}
