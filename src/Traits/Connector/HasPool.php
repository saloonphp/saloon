<?php

declare(strict_types=1);

namespace Saloon\Traits\Connector;

use Saloon\Http\Pool;
use Saloon\Contracts\Pool as PoolContract;

trait HasPool
{
    /**
     * Create a request pool
     *
     * @param iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request>|callable(\Saloon\Contracts\Connector): iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request> $requests
     * @param int|callable(int $pendingRequests): (int) $concurrency
     * @param callable(\Saloon\Contracts\Response, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $responseHandler
     * @param callable(mixed $reason, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $exceptionHandler
     * @return \Saloon\Contracts\Pool
     */
    public function pool(iterable|callable $requests = [], int|callable $concurrency = 5, callable|null $responseHandler = null, callable|null $exceptionHandler = null): PoolContract
    {
        return new Pool($this, $requests, $concurrency, $responseHandler, $exceptionHandler);
    }
}
