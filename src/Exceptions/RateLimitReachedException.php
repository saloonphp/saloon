<?php

declare(strict_types=1);

namespace Saloon\Exceptions;

use Saloon\Http\RateLimiting\Limit;

class RateLimitReachedException extends SaloonException
{
    public function __construct(readonly protected Limit $limit)
    {
        parent::__construct(sprintf('Request Rate Limit Reached! Limiter: %s', $this->limit->getId()));
    }

    public function getLimit(): Limit
    {
        return $this->limit;
    }
}
