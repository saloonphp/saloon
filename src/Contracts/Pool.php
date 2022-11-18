<?php

namespace Saloon\Contracts;

use Generator;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\InvalidPoolItemException;
use Saloon\Exceptions\SaloonException;

interface Pool
{
    /**
     * Specify a callback to happen for each successful request
     *
     * @param callable $callable
     * @return \Saloon\Http\Pool
     */
    public function withResponseHandler(callable $callable): static;

    /**
     * Specify a callback to happen for each failed request
     *
     * @param callable $callable
     * @return \Saloon\Http\Pool
     */
    public function withExceptionHandler(callable $callable): static;

    /**
     * Set the amount of concurrent requests that should be sent
     *
     * @param int|callable $concurrency
     * @return \Saloon\Http\Pool
     */
    public function setConcurrency(int|callable $concurrency): \Saloon\Http\Pool;

    /**
     * Set the requests
     *
     * @param callable|iterable $requests
     * @return \Saloon\Http\Pool
     */
    public function setRequests(callable|iterable $requests): \Saloon\Http\Pool;

    /**
     * Get the request generator
     *
     * @return Generator
     */
    public function getRequests(): Generator;

    /**
     * Send the pool and create a Promise
     *
     * @return PromiseInterface
     * @throws InvalidPoolItemException
     * @throws SaloonException
     * @throws \ReflectionException
     */
    public function send(): PromiseInterface;
}
