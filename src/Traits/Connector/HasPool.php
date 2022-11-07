<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\Connector;

use Sammyjo20\Saloon\Http\Pool;
use Sammyjo20\Saloon\Exceptions\SaloonException;
use Sammyjo20\Saloon\Exceptions\InvalidPoolItemException;

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
    public function pool(iterable|callable $requests = [], int|callable $concurrency = 5, callable|null $responseHandler = null, callable|null $exceptionHandler = null): Pool
    {
        return new Pool($this, $requests, $concurrency, $responseHandler, $exceptionHandler);
    }
}
