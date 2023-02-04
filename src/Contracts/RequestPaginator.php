<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;

/**
 * @template TRequest of \Saloon\Contracts\Request
 * @template TResponse of \Saloon\Contracts\Response
 *
 * @extends \Iterator<TResponse|\GuzzleHttp\Promise\PromiseInterface>
 */
interface RequestPaginator extends Iterator
{
    /**
     * @param  (callable(int $pendingRequests): int)|int $concurrency
     * @param  (callable(TResponse $response, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): void)|null $responseHandler
     * @param  (callable(mixed $reason, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): void)|null $exceptionHandler
     */
    public function pool(
        callable|int $concurrency = 5,
        callable|null $responseHandler = null,
        callable|null $exceptionHandler = null,
    ): Pool;

    /**
     * @return $this
     */
    public function async(bool $async = true): static;

    public function isAsync(): bool;

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
     */
    public function previous(): void;
}
