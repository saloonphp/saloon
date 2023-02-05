<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use GuzzleHttp\Promise\PromiseInterface;

interface Pool
{
    /**
     * Specify a callback to happen for each successful request
     *
     * @param callable(\Saloon\Contracts\Response, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void) $callable
     * @return $this
     */
    public function withResponseHandler(callable $callable): static;

    /**
     * Specify a callback to happen for each failed request
     *
     * @param callable(mixed $reason, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void) $callable
     * @return $this
     */
    public function withExceptionHandler(callable $callable): static;

    /**
     * Set the amount of concurrent requests that should be sent
     *
     * @param int|callable(int $pendingRequests): (int) $concurrency
     * @return $this
     */
    public function setConcurrency(int|callable $concurrency): static;

    /**
     * Set the requests
     *
     * @param iterable<array-key, \GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request>|callable(\Saloon\Contracts\Connector): (iterable<array-key, \GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request>) $requests
     * @return $this
     */
    public function setRequests(iterable|callable $requests): static;

    /**
     * Get the request generator
     *
     * @return iterable<array-key, \GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request>
     */
    public function getRequests(): iterable;

    /**
     * Send the pool and create a Promise
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function send(): PromiseInterface;
}
