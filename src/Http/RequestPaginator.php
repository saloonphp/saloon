<?php

declare(strict_types=1);

namespace Saloon\Http;

use Closure;
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

abstract class RequestPaginator implements RequestPaginatorContract, SerialisableRequestPaginator
{
    /**
     * @var \Closure(\Saloon\Contracts\Response): bool
     */
    protected readonly Closure $hasNextPage;

    /**
     * @var \Saloon\Contracts\Response|null
     */
    protected Response|null $lastResponse = null;

    /**
     * @param \Saloon\Contracts\Connector $connector
     * @param \Saloon\Contracts\Request $originalRequest
     * @param int|null $limit
     * @param callable(\Saloon\Contracts\Response): bool $hasNextOriginalPage
     */
    public function __construct(
        protected readonly Connector $connector,
        protected readonly Request $originalRequest,
        protected readonly int|null $limit,
        callable $hasNextOriginalPage,
    ) {
        $this->hasNextPage = $hasNextOriginalPage(...);
    }

    /**
     * @param  (callable(int $pendingRequests): int)|int $concurrency
     * @param  (callable(\Saloon\Contracts\Response $response, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): void)|null $responseHandler
     * @param  (callable(mixed $reason, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): void)|null $exceptionHandler
     */
    public function pool(
        callable|int $concurrency = 5,
        callable|null $responseHandler = null,
        callable|null $exceptionHandler = null,
    ): Pool {
        return $this->connector->pool(
            $this,
            $concurrency,
            $responseHandler,
            $exceptionHandler,
        );
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
}
