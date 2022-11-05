<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\Connector;

use Closure;
use Generator;
use Sammyjo20\Saloon\Http\Pool;

trait HasPool
{
    /**
     * Create a request pool
     *
     * @param iterable|Generator|Closure|callable $requests
     * @param int $concurrentRequests
     * @return Pool
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\InvalidPoolItemException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    public function pool(iterable|Generator|Closure|callable $requests = [], int $concurrentRequests = 5): Pool
    {
        return new Pool($this, $requests, $concurrentRequests);
    }
}
