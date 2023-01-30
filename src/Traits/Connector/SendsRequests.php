<?php

declare(strict_types=1);

namespace Saloon\Traits\Connector;

use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\MockClient;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\PendingRequest as PendingRequestContract;

trait SendsRequests
{
    /**
     * Send a request
     *
     * @param \Saloon\Contracts\Request $request
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \Saloon\Contracts\Response
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     * @throws \Saloon\Exceptions\PendingRequestException
     */
    public function send(Request $request, MockClient $mockClient = null): Response
    {
        // 🚀 ... 🪐  ... 💫

        return $this->createPendingRequest($request, $mockClient)->send();
    }

    /**
     * Send a synchronous request with retries
     *
     * @param \Saloon\Contracts\Request $request
     * @param int $attempts
     * @param int $interval
     * @param callable|null $handleRetry
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return mixed
     */
    public function sendWithRetry(Request $request, int $attempts, int $interval = 0, callable $handleRetry = null, MockClient $mockClient = null): Response
    {
        // Todo:
        // - ShouldRetry should accept two arguments: Response and PendingRequest
        // - This will allow user to customise the PendingRequest
        // - Check if someone has changed it to async before the pending request is sent because that'll end badly
        // - Wait between attempts
        // - Perhaps use while with sleep (while ! $response->failed() || $currentAttempts <= $attempts
    }

    /**
     * Send a request asynchronously
     *
     * @param \Saloon\Contracts\Request $request
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     * @throws \Saloon\Exceptions\PendingRequestException
     */
    public function sendAsync(Request $request, MockClient $mockClient = null): PromiseInterface
    {
        // 🚀 ... 🪐  ... 💫

        return $this->createPendingRequest($request, $mockClient)->sendAsync();
    }

    /**
     * Create a new PendingRequest
     *
     * @param \Saloon\Contracts\Request $request
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \Saloon\Contracts\PendingRequest
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     * @throws \Saloon\Exceptions\PendingRequestException
     */
    public function createPendingRequest(Request $request, MockClient $mockClient = null): PendingRequestContract
    {
        return new PendingRequest($this, $request, $mockClient);
    }
}
