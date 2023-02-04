<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use Saloon\Contracts\Connector;
use Saloon\Contracts\Pool;
use Saloon\Contracts\Request;
use Saloon\Contracts\RequestPaginator as RequestPaginatorContract;
use Saloon\Contracts\Response;
use Saloon\Contracts\SerialisableRequestPaginator;

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
     * @var TResponse|null
     */
    protected Response|null $lastResponse = null;

    /**
     * @param \Saloon\Contracts\Connector $connector
     * @param TRequest $originalRequest
     * @param int|null $limit
     */
    public function __construct(
        protected readonly Connector $connector,
        protected readonly Request $originalRequest,
        protected readonly int|null $limit,
    ) {}

    /**
     * @param callable(int $pendingRequests): (int)|int $concurrency
     * @param callable(TResponse $response, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $responseHandler
     * @param callable(mixed $reason, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $exceptionHandler
     * @return \Saloon\Contracts\Pool
     */
    public function pool(
        callable|int $concurrency = 5,
        callable|null $responseHandler = null,
        callable|null $exceptionHandler = null,
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
     * @return int|null
     */
    public function limit(): ?int
    {
        return $this->limit;
    }

    /**
     * @return \Saloon\Contracts\Response|null
     */
    public function lastResponse(): ?Response
    {
        return $this->lastResponse;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        // Nullify the last response, so we don't accidentally check if there are more pages, even though we're starting over.
        $this->lastResponse = null;
    }

    /**
     * @return array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit: int|null,
     * }
     */
    public function __serialize(): array
    {
        // TODO: figure out how to serialise the 'has next page' resolver/callback.

        return [
            'connector' => $this->connector,
            'original_request' => $this->originalRequest,
            'limit' => $this->limit,
        ];
    }

    /**
     * @param array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit: int|null,
     * } $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->connector = $data['connector'];
        $this->originalRequest = $data['original_request'];
        $this->limit = $data['limit'];
    }
}
