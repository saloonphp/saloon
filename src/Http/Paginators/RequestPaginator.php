<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Saloon\Contracts\Connector;
use Saloon\Contracts\Pool;
use Saloon\Contracts\Request;
use Saloon\Contracts\RequestPaginator as RequestPaginatorContract;
use Saloon\Contracts\Response;
use Saloon\Traits\Request\HasPagination;

// TODO 1: Look into serialising the Connector and original Request,
//           to ensure that we can rebuild the paginator state without storing the entire multiverse.

abstract class RequestPaginator implements RequestPaginatorContract
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
     * @param string|null $property
     *
     * @return iterable<array-key, mixed>
     */
    public function json(string $property = null): iterable
    {
        foreach ($this as $response) {
            yield $response->json($property);
        }
    }

    /**
     * @param string|null $property
     * @param bool $lazy
     *
     * @return ($lazy is true ? \Illuminate\Support\LazyCollection<array-key, mixed> : \Illuminate\Support\Collection<array-key, mixed>)
     */
    public function collect(string $property = null, bool $lazy = true): LazyCollection|Collection
    {
        return LazyCollection::make($this->json($property))
            ->unless($lazy)->collect();
    }

    /**
     * Called by this base RequestPaginator, when it's rewinding.
     *
     * @return void
     */
    abstract protected function reset(): void;

    /**
     * Apply paging information, like setting the Request query parameter 'page', or 'offset', etc.
     *
     * @param \Saloon\Contracts\Request $request
     *
     * @return void
     */
    abstract protected function applyPagination(Request $request): void;

    abstract protected function isFinished(): bool;

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
            || $this->isFinished();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->totalPages();
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        if (! $this->shouldRewind()) {
            return;
        }

        $this->reset();

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

        return ! $this->isFinished();
    }

    /**
     * @return \Saloon\Contracts\Response|\GuzzleHttp\Promise\PromiseInterface
     *
     * @TODO: Proper return type hint, for tools to resolve when either one is returned (async or not).
     */
    public function current(): Response|PromiseInterface
    {
        $this->applyPagination(
            $request = clone $this->originalRequest,
        );

        if (! $this->isAsync()) {
            return $this->currentResponse = $this->connector->send($request);
        }

        $promise = $this->connector->sendAsync($request);

        // If we don't wait for the 'first' response, we won't know how many iterations we need.
        // Awaiting one response is a small price to pay.
        if (is_null($this->currentResponse)) {
            $this->currentResponse = $promise->wait();
        }

        return $promise;
    }
}
