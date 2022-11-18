<?php declare(strict_types=1);

namespace Saloon\Traits\Connector;

use Saloon\Contracts\Pool as PoolContract;
use Saloon\Exceptions\SaloonException;
use Saloon\Exceptions\InvalidPoolItemException;
use Saloon\Http\Pool;

trait HasPool
{
    /**
     * Create a request pool
     *
     * @param iterable|callable $requests
     * @param int|callable $concurrency
     * @param callable|null $responseHandler
     * @param callable|null $exceptionHandler
     * @return Pool
     * @throws \ReflectionException
     * @throws InvalidPoolItemException
     * @throws SaloonException
     */
    public function pool(iterable|callable $requests = [], int|callable $concurrency = 5, callable|null $responseHandler = null, callable|null $exceptionHandler = null): PoolContract
    {
        return new Pool($this, $requests, $concurrency, $responseHandler, $exceptionHandler);
    }
}
