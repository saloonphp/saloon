<?php

namespace Sammyjo20\Saloon\Traits\Connector;

use Closure;
use Generator;
use Sammyjo20\Saloon\Http\Pool;

trait HasPool
{
    /**
     * Create a request pool
     *
     * @param array $requests
     * @param int $concurrentRequests
     * @return Pool
     */
    public function pool(array|Generator|Closure|callable $requestPayload, int $concurrentRequests = 5): Pool
    {
        return new Pool($this, $requestPayload, $concurrentRequests);
    }
}
