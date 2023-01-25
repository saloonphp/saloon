<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use GuzzleHttp\Promise\PromiseInterface;

interface Pool
{
    /**
     * Specify a callback to happen for each successful request
     *
     * @param callable $callable
     * @return $this
     */
    public function withResponseHandler(callable $callable): static;

    /**
     * Specify a callback to happen for each failed request
     *
     * @param callable $callable
     * @return $this
     */
    public function withExceptionHandler(callable $callable): static;

    /**
     * Set the amount of concurrent requests that should be sent
     *
     * @param int|callable $concurrency
     * @return $this
     */
    public function setConcurrency(int|callable $concurrency): static;

    /**
     * Set the requests
     *
     * @param iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request>|callable $requests
     * @return $this
     */
    public function setRequests(iterable|callable $requests): static;

    /**
     * Get the request generator
     *
     * @return iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request>
     */
    public function getRequests(): iterable;

    /**
     * Send the pool and create a Promise
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function send(): PromiseInterface;
}
