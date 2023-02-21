<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Iterator;
use Countable;
use ReturnTypeWillChange;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * @extends  \Iterator<array-key, \Saloon\Contracts\Response|\GuzzleHttp\Promise\PromiseInterface>
 */
interface Paginator extends Countable, Iterator
{
    /**
     * Create a paginator pool
     *
     * @param callable(int $pendingRequests): (int)|int $concurrency
     * @param callable(Response $response, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $responseHandler
     * @param callable(mixed $reason, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $exceptionHandler
     */
    public function pool(
        callable|int $concurrency = 5,
        ?callable    $responseHandler = null,
        ?callable    $exceptionHandler = null,
    ): Pool;

    /**
     * Set the asynchronous mode on the paginator
     *
     * @return $this
     */
    public function async(bool $async = true): static;

    /**
     * Checks if the paginator will return asynchronous requests or not
     *
     * @return bool
     */
    public function isAsync(): bool;

    /**
     * Checks if rewinding is enabled
     *
     * @return bool
     */
    public function rewindingEnabled(): bool;

    /**
     * Makes the Paginator continue where it left off in an earlier loop, instead of rewinding/'resetting' when used in a new loop.
     *
     * @param bool $enableRewinding
     * @return $this
     */
    public function enableRewinding(bool $enableRewinding = true): static;

    /**
     * Returns whether the Paginator will continue where it left off in a previous loop, or not.
     *
     * @return bool
     *
     * @see \Saloon\Contracts\Paginator::enableRewinding()
     */
    public function shouldRewind(): bool;

    /**
     * Total entries this requested resource has, regardless of amount of queries done or that will be made.
     *
     * @return int
     */
    public function totalResults(): int;

    /**
     * The limit of the paginator, like per-page.
     *
     * @return int|null
     */
    public function limit(): ?int;

    /**
     * Total pages the requested resource has, given the payload, query parameters, etc, that are sent.
     *
     * @return int
     */
    public function totalPages(): int;

    /**
     * Iterate through a JSON array of results, key can be provided like "results".
     *
     * @param string|null $key
     *
     * @return iterable<array-key, mixed>
     */
    public function json(string $key = null): iterable;

    /**
     * Create a Laravel collection for the results.
     *
     * Will return a collection of responses or a key can be provided to iterate over results.
     *
     * @param string|null $key
     * @param bool $lazy
     * @param bool $collapse
     * @return ($lazy is true ? \Illuminate\Support\LazyCollection<array-key, mixed> : \Illuminate\Support\Collection<array-key, mixed>)
     */
    public function collect(string $key = null, bool $lazy = true, bool $collapse = true): LazyCollection|Collection;

    /**
     * Rewind the iterator
     *
     * @return void
     */
    public function rewind(): void;

    /**
     * Check if the iterator is still valid
     *
     * @return bool
     */
    public function valid(): bool;

    /**
     * Get the current response in the iterator
     *
     * @return \Saloon\Contracts\Response|\GuzzleHttp\Promise\PromiseInterface
     */
    public function current(): Response|PromiseInterface;

    /**
     * Get the current key of the iterator
     *
     * @return string|int
     */
    #[ReturnTypeWillChange]
    public function key(): string|int;

    /**
     * Move the iterator to the next item
     *
     * @return void
     */
    public function next(): void;

    /**
     * Set the limit of the paginator
     *
     * @param int|null $limit
     * @return $this
     */
    public function setLimit(?int $limit): static;

    /**
     * Get the limit key name
     *
     * @return string
     */
    public function getLimitKeyName(): string;

    /**
     * Get the total key name
     *
     * @return string
     */
    public function getTotalKeyName(): string;
}
