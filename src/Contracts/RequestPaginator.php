<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Countable;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use ReturnTypeWillChange;

/**
 * @template TRequest of \Saloon\Contracts\Request
 * @template TResponse of \Saloon\Contracts\Response
 *
 * @extends \Iterator<TResponse|\GuzzleHttp\Promise\PromiseInterface>
 */
interface RequestPaginator extends Countable, Iterator
{
    /**
     * @param callable(int $pendingRequests): (int)|int $concurrency
     * @param callable(TResponse $response, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $responseHandler
     * @param callable(mixed $reason, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $exceptionHandler
     */
    public function pool(
        callable|int $concurrency = 5,
        ?callable $responseHandler = null,
        ?callable $exceptionHandler = null,
    ): Pool;

    /**
     * @return $this
     */
    public function async(bool $async = true): static;

    /**
     * @return bool
     */
    public function isAsync(): bool;

    /**
     * Makes the Paginator continue where it left off in an earlier loop, instead of rewinding/'resetting' when used in a new loop.
     *
     * Iterables are, by nature, rewinding ('resetting') whenever you use it in a new loop.
     * I.e., if you go through 2 iterations in a loop, then break out of it, and iterate again in a new loop, it'll be rewound to the first element.
     * This makes sure that the rewinding step is skipped.
     *
     * @param bool $ignoreRewinding
     *
     * @return $this
     *
     * @TODO Come up with a better name for this method.
     */
    public function ignoreRewinding(bool $ignoreRewinding = true): static;

    /**
     * Returns whether or not the Paginator will continue where it left off in a previous loop, or not.
     *
     * @return bool
     *
     * @see \Saloon\Contracts\RequestPaginator::ignoreRewinding()
     *
     * @TODO Come up with a better name for this method.
     */
    public function shouldRewind(): bool;

    /**
     * @return int|null
     *
     * @TODO: Does all type of paging actually have limit, or should this be extracted somewhere else?
     */
    public function limit(): ?int;

    /**
     * @return int Total entries this requested resource has, regardless of amount of queries done or that will be made.
     */
    public function totalEntries(): int;

    /**
     * @return iterable<array-key, mixed>
     *
     * @TODO entry data type
     */
    public function entries(): iterable;

    /**
     * @return int Total pages the requested resource has, given the payload, query parameters, etc, that are sent.
     *
     * @TODO: Make naming more abstract and versatile.
     *        F.e., page, offset
     */
    public function totalPages(): int;

    /**
     * @return string|int
     *
     * @TODO: Make naming more abstract and versatile.
     *        F.e., page, offset
     */
    #[ReturnTypeWillChange]
    public function firstPage(): string|int;

    /**
     * @return int|null Previous page number, or null if there is no previous page.
     *
     * @TODO: Make naming more abstract and versatile.
     *        F.e., page, offset
     */
    public function previousPage(): ?int;

    /**
     * @return bool
     *
     * @TODO: Make naming more abstract and versatile.
     *        F.e., page, offset
     */
    public function hasPreviousPage(): bool;

    /**
     * @return int Current page number.
     *
     * @TODO: Make naming more abstract and versatile.
     *        F.e., page, offset
     */
    public function currentPage(): int;

    /**
     * @return int|null Next page number, or null if there is no next page.
     *
     * @TODO: Make naming more abstract and versatile.
     *        F.e., page, offset
     */
    public function nextPage(): ?int;

    /**
     * @return bool
     *
     * @TODO: Make naming more abstract and versatile.
     *        F.e., page, offset
     */
    public function hasNextPage(): bool;

    /**
     * @return string|int
     *
     * @TODO: Make naming more abstract and versatile.
     *        F.e., page, offset
     */
    #[ReturnTypeWillChange]
    public function lastPage(): string|int;

    /**
     * The iteration methods are defined in the order PHP executes them.
     * 0. {@see Iterator::rewind()}    - reset to first position (only run when it's first being iterated)
     * --------------------------------------------------------------------------------------------
     * 1. {@see Iterator::valid()}     - check if the iterator is valid (current position has an item)
     * 2. {@see Iterator::current()}   - retrieve the item in the current position
     * 3. {@see Iterator::key()}       - retrieve the current position
     * 4. {@see Iterator::next()}      - increase the position
     * --------------------------------------------------------------------------------------------
     * Because of the order-, and how things are executed, it's recommended to retrieve an item in (2), as opposed to (4).
     * I.e., only use (4) to increase the 'page' number (actual page, offset, etc), and then retrieve- and return the response.
     */

    /**
     * @return void
     */
    public function rewind(): void;

    /**
     * @return bool
     */
    public function valid(): bool;

    /**
     * @return ($this->async is true ? \GuzzleHttp\Promise\PromiseInterface : \Saloon\Contracts\Response)
     *
     * @TODO: Check if `$this->async` is actually valid, or if we need to reassess how to properly type this.
     */
    public function current(): Response|PromiseInterface;

    /**
     * @return string|int
     */
    public function key(): string|int;

    /**
     * @return void
     */
    public function next(): void;

    /**
     * Go to the previous page.
     * Note that this method is *not* part of PHP's {@see \Iterator}, and will not work in loops, unless manually called.
     *
     * @return void
     *
     * @example Examples on usage, since it can't be used in traditional ways.
     * <code>
     * $connector = new MyApiConnector;
     * $iterator = $connector->paginate(GetMyResourcesRequest::make());
     *
     * for (; $iterator->valid(); $iterator->previous()) {
     *     // $index = $iterator->key();
     *     // $response = $iterator->current();
     *     // ...
     * }
     *
     * while ($iterator->valid()) {
     *     // $index = $iterator->key();
     *     // $response = $iterator->current();
     *     // ...
     *     // $iterator->next();
     * }
     * </code>
     *
     * You can also, for whatever reason, use it to re-do a request.
     * <code>
     * $connector = new MyApiConnector;
     * $iterator = $connector->paginate(GetMyResourcesRequest::make());
     *
     * foreach ($iterator as $response) {
     *     // $index = $iterator->key();
     *     // $response = $iterator->current();
     *     // ...
     *     // $iterator->previous();
     * }
     * </code>
     */
    public function previous(): void;
}
