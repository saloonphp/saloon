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
use Saloon\Traits\Request\HasPagination;

// TODO 1: Look into serialising the Connector and original Request,
//           to ensure that we can rebuild the paginator state without storing the entire multiverse.

abstract class RequestPaginator implements RequestPaginatorContract, SerialisableRequestPaginator
{
    use HasPagination;

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
     * @see \Saloon\Contracts\RequestPaginator::enableRewinding()
     *
     * @TODO Come up with a better name for this method.
     */
    protected bool $rewindingEnabled = false;

    /**
     * @var \Saloon\Contracts\Response|null
     */
    protected ?Response $currentResponse = null;

    /**
     * @param \Saloon\Contracts\Connector $connector
     * @param \Saloon\Contracts\Request $originalRequest
     * @param int|null $limit
     */
    public function __construct(
        protected readonly Connector $connector,
        protected readonly Request $originalRequest,
        protected readonly ?int $limit = null,
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
     * @param callable(\Saloon\Contracts\Response $response, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $responseHandler
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
     * @return bool
     */
    public function rewindingEnabled(): bool
    {
        return $this->rewindingEnabled;
    }

    /**
     * @param bool $enableRewinding
     *
     * @return $this
     */
    public function enableRewinding(bool $enableRewinding = true): static
    {
        $this->rewindingEnabled = $enableRewinding;

        return $this;
    }

    /**
     * @return bool
     */
    public function shouldRewind(): bool
    {
        return $this->rewindingEnabled()
            || $this->isLastPage();
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
     * @return bool
     */
    public function isFirstPage(): bool
    {
        return $this->currentPage() === $this->firstPage();
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
     * @return bool
     */
    public function isLastPage(): bool
    {
        return $this->currentPage() === $this->lastPage();
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
     * @return \Saloon\Contracts\Response|\GuzzleHttp\Promise\PromiseInterface
     *
     * @TODO: Proper return type hint, for tools to resolve when either one is returned (async or not).
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
     *     limit_name: string,
     *     limit: int|null,
     *     rewinding_enabled: bool,
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
     *     limit_name: string,
     *     limit: int|null,
     *     rewinding_enabled: bool,
     * }
     */
    public function __serialize(): array
    {
        return [
            'connector' => $this->connector,
            'original_request' => $this->originalRequest,
            'limit_name' => $this->limitName,
            'limit' => $this->limit,
            'rewinding_enabled' => $this->rewindingEnabled,
        ];
    }

    /**
     * @param array{
     *     connector: \Saloon\Contracts\Connector,
     *     original_request: \Saloon\Contracts\Request,
     *     limit_name: string,
     *     limit: int|null,
     *     rewinding_enabled: bool,
     * } $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->connector = $data['connector'];
        $this->originalRequest = $data['original_request'];
        $this->limitName = $data['limit_name'];
        $this->limit = $data['limit'];
        $this->rewindingEnabled = $data['rewinding_enabled'];
    }
}
