<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use Generator;
use Saloon\Contracts\Pool;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Contracts\Connector;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\Paginator as PaginatorContract;
use Saloon\Exceptions\PaginatorException;

abstract class Paginator implements PaginatorContract
{
    /**
     * The query parameter key for the limit
     *
     * @var string
     */
    protected string $limitKeyName = 'limit';

    /**
     * The JSON key name for the results
     *
     * @var string
     */
    protected string $resultsKeyName = 'data';

    /**
     * Check if the paginator will use asynchronous responses or not
     *
     * @var bool
     */
    protected bool $async = false;

    /**
     * Whether the Paginator will skip rewinding
     *
     * @var bool
     */
    protected bool $rewindingEnabled = false;

    /**
     * The current response in the iterator
     *
     * @var \Saloon\Contracts\Response|null
     */
    protected ?Response $currentResponse = null;

    /**
     * Constructor
     *
     * @param \Saloon\Contracts\Connector $connector
     * @param \Saloon\Contracts\Request $originalRequest
     * @param int|null $limit
     */
    public function __construct(
        protected readonly Connector $connector,
        protected readonly Request   $originalRequest,
        protected readonly ?int      $limit = null,
    )
    {
        //
    }

    /**
     * Iterate through a JSON array of results, key can be provided like "results".
     *
     * @param string|null $key
     *
     * @return iterable<array-key, ($key is null ? Response : mixed)>
     */
    public function json(string $key = null): iterable
    {
        foreach ($this as $response) {
            yield $response->json($key);
        }
    }

    /**
     * Create a Laravel collection for the results.
     *
     * Will return a collection of responses or a key can be provided to iterate over results.
     *
     * @param string|null $key
     * @param bool $lazy
     * @param bool $collapse
     * @return ($lazy is true ? \Illuminate\Support\LazyCollection<array-key, ($key is null ? Response : mixed)> : \Illuminate\Support\Collection<array-key, ($key is null ? Response : mixed)>)
     */
    public function collect(string $key = null, bool $lazy = true, bool $collapse = true): LazyCollection|Collection
    {
        $collection = LazyCollection::make(function () use ($key): Generator {
            return isset($key) ? yield from $this->json($key) : yield from $this;
        });

        if (isset($key) && $collapse === true) {
            $collection = $collection->collapse();
        }

        return $lazy === true ? $collection : $collection->collect();
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

    /**
     * Check if the paginator has any more pages
     *
     * @return bool
     */
    abstract protected function isFinished(): bool;

    /**
     * Create a paginator pool
     *
     * @param callable(int $pendingRequests): (int)|int $concurrency
     * @param callable(\Saloon\Contracts\Response $response, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $responseHandler
     * @param callable(mixed $reason, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $exceptionHandler
     * @return \Saloon\Contracts\Pool
     */
    public function pool(
        callable|int $concurrency = 5,
        ?callable    $responseHandler = null,
        ?callable    $exceptionHandler = null,
    ): Pool
    {
        // The purpose of a pool is to concurrently send requests. So 'force' set async.

        $this->async();

        return $this->connector->pool(
            $this,
            $concurrency,
            $responseHandler,
            $exceptionHandler,
        );
    }

    /**
     * Set the asynchronous mode on the paginator
     *
     * @param bool $async
     * @return $this
     */
    public function async(bool $async = true): static
    {
        $this->async = $async;

        return $this;
    }

    /**
     * Checks if the paginator will return asynchronous requests or not
     *
     * @return bool
     */
    public function isAsync(): bool
    {
        return $this->async;
    }

    /**
     * Checks if rewinding is enabled
     *
     * @return bool
     */
    public function rewindingEnabled(): bool
    {
        return $this->rewindingEnabled;
    }

    /**
     * Makes the Paginator continue where it left off in an earlier loop, instead of rewinding/'resetting' when used in a new loop.
     *
     * @param bool $enableRewinding
     * @return $this
     */
    public function enableRewinding(bool $enableRewinding = true): static
    {
        $this->rewindingEnabled = $enableRewinding;

        return $this;
    }

    /**
     * Returns whether the Paginator will continue where it left off in a previous loop, or not.
     *
     * @return bool
     */
    public function shouldRewind(): bool
    {
        return $this->rewindingEnabled() || $this->isFinished();
    }

    /**
     * Count the total pages in the paginator
     *
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
        // No need to rewind if we have no Response.
        // I also brain-farted this one, as isFinished() usually checks for 'total pages' on the Response.
        // If it doesn't have a Response, it'll call current(), causing a problematic bug.
        // TODO: What will happen when we implement things like serialisation,
        //         and the response will be null again, even though we're not on the first request?
        if (is_null($this->currentResponse)) {
            return;
        }

        if (! $this->shouldRewind()) {
            // When we break out of loops, next() won't be called.
            // Because rewind() is then called on a new loop, we need to manually instruct a next().
            // Otherwise we'll end up sending a new request for the latest retrieved page.
            $this->next();

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

        ray(! $this->isFinished());

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

    /**
     * Set the query parameter key for the limit
     *
     * @param string $limitName
     * @return $this
     */
    public function setLimitKeyName(string $limitName): static
    {
        $this->limitKeyName = $limitName;

        return $this;
    }

    /**
     * Get the query parameter key for the limit
     *
     * @return string
     */
    public function getLimitKeyName(): string
    {
        return $this->limitKeyName;
    }

    /**
     * Get the limit of the paginator
     *
     * @return int|null
     */
    public function limit(): ?int
    {
        return $this->limit;
    }

    /**
     * Calculate the total results
     *
     * @return int
     * @throws \Saloon\Exceptions\PaginatorException
     */
    public function totalResults(): int
    {
        if (is_null($this->currentResponse)) {
            $this->current();
        }

        // Todo: Switch to total key
        $total = $this->currentResponse->json('total');

        if (is_null($total)) {
            throw new PaginatorException('Unable to calculate the total results from the response. Make sure the total key is correct.');
        }

        return $total;
    }

    /**
     * Get the total pages in the result set
     *
     * @return int
     * @throws \Saloon\Exceptions\PaginatorException
     */
    public function totalPages(): int
    {
        return (int)ceil($this->totalResults() / $this->limit);
    }

    /**
     * Set the results key name
     *
     * @param string $resultsKeyName
     * @return PageRequestPaginator
     */
    public function setResultsKeyName(string $resultsKeyName): static
    {
        $this->resultsKeyName = $resultsKeyName;

        return $this;
    }
}
