<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Closure;
use Saloon\Http\Request as RequestContract;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\FatalRequestException;

class RetryConnector extends TestConnector
{
    public function __construct(?int $tries = null, int $retryInterval = 0, ?bool $throwOnMaxTries = null, protected ?Closure $handleRetry = null)
    {
        // These are just for us to test the various retries

        $this->tries = $tries;
        $this->retryInterval = $retryInterval;
        $this->throwOnMaxTries = $throwOnMaxTries;
    }

    public function handleRetry(FatalRequestException|RequestException $exception, RequestContract $request): bool
    {
        return isset($this->handleRetry) ? call_user_func($this->handleRetry, $exception, $request) : true;
    }
}
