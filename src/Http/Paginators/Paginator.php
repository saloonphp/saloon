<?php

declare(strict_types=1);

namespace Saloon\Http\Paginators;

use Generator;
use Saloon\Contracts\Pool;
use Saloon\Traits\Makeable;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Contracts\Connector;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\PaginatorException;
use Saloon\Contracts\Paginator as PaginatorContract;

abstract class Paginator implements PaginatorContract
{
    use Makeable;

    /**
     * The query parameter key for the limit
     *
     * @var string
     */
    protected string $limitKeyName = 'limit';

    /**
     * The JSON key name for the total
     *
     * @var string
     */
    protected string $totalKeyName = 'total';

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
        protected ?int               $limit = null,
    ) {
        $this->configure();
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
     * Called by this base paginator, when it's rewinding.
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
    ): Pool {
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
     * @throws \Saloon\Exceptions\PaginatorException
     */
    public function count(): int
    {
        return $this->totalPages();
    }

    /**
     * Rewind the paginator back to the beginning
     *
     * @return void
     */
    public function rewind(): void
    {
        if (is_null($this->currentResponse)) {
            return;
        }

        if (! $this->shouldRewind()) {
            // When we break out of loops, next() won't be called. Because rewind() is then called
            // on a new loop, we need to manually instruct a next(). Otherwise we'll end up
            // sending a new request for the latest retrieved page.

            $this->next();

            return;
        }

        // Run the reset method on the paginator instance, so it can perform any property
        // resets that they need.

        $this->reset();

        // Nullify the current response, so we don't accidentally check if there are
        // more pages, even though we're starting over.

        $this->currentResponse = null;
    }

    /**
     * Check if the iterator is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        // If we haven't made any requests yet, then the iterator is valid.

        if (is_null($this->currentResponse)) {
            return true;
        }

        return ! $this->isFinished();
    }

    /**
     * Retrieve the current page
     *
     * @return \Saloon\Contracts\Response|\GuzzleHttp\Promise\PromiseInterface
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

        $total = $this->currentResponse->json($this->totalKeyName);

        if (is_null($total)) {
            throw new PaginatorException('Unable to calculate the total results from the response. Make sure the total key is correct.');
        }

        return (int) $total;
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
     * Set the JSON key name for total
     *
     * @param string $totalKeyName
     * @return $this
     */
    public function setTotalKeyName(string $totalKeyName): static
    {
        $this->totalKeyName = $totalKeyName;

        return $this;
    }

    /**
     * Set the limit of the paginator
     *
     * @param int|null $limit
     * @return $this
     */
    public function setLimit(?int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Get the limit key name
     *
     * @return string
     */
    public function getLimitKeyName(): string
    {
        return $this->limitKeyName;
    }

    /**
     * Get the total key name
     *
     * @return string
     */
    public function getTotalKeyName(): string
    {
        return $this->totalKeyName;
    }

    /**
     * Configure the paginator
     *
     * @return void
     */
    protected function configure(): void
    {
        //
    }
}
