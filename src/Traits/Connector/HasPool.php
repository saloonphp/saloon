<?php

declare(strict_types=1);

namespace Saloon\Traits\Connector;

use Saloon\Http\Pool;

trait HasPool
{
    /**
     * Create a request pool
     *
     * @param iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Http\Request>|callable(\Saloon\Http\Connector): iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Http\Request> $requests
     * @param int|callable(int $pendingRequests): (int) $concurrency
     * @param callable(\Saloon\Http\Response, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $responseHandler
     * @param callable(mixed $reason, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $exceptionHandler
     */
    public function pool(iterable|callable $requests = [], int|callable $concurrency = 5, callable|null $responseHandler = null, callable|null $exceptionHandler = null): Pool
    {
        return new Pool($this, $requests, $concurrency, $responseHandler, $exceptionHandler);
    }
}
