<?php

declare(strict_types=1);

namespace Saloon\Traits\Connector;

use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Contracts\MockClient;
use GuzzleHttp\Promise\PromiseInterface;

trait SendsRequests
{
    /**
     * Send a request
     *
     * @template TRequest of \Saloon\Contracts\Request
     *
     * @param TRequest $request
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \Saloon\Contracts\Response<TRequest>
     */
    public function send(Request $request, MockClient $mockClient = null): Response
    {
        $request->setConnector($this);

        return $request->createPendingRequest($mockClient)->send();
    }

    /**
     * Send a request asynchronously
     *
     * @param \Saloon\Contracts\Request $request
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function sendAsync(Request $request, MockClient $mockClient = null): PromiseInterface
    {
        $request->setConnector($this);

        return $request->createPendingRequest($mockClient)->sendAsync();
    }
}
