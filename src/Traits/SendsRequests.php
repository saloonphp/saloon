<?php

namespace Sammyjo20\Saloon\Traits;

use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Managers\RequestManager;
use Sammyjo20\Saloon\Exceptions\SaloonException;

trait SendsRequests
{
    /**
     * Send the request synchronously.
     *
     * @param MockClient|null $mockClient
     * @param bool $asynchronous
     * @return SaloonResponse
     * @throws SaloonException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     */
    public function send(MockClient $mockClient = null, bool $asynchronous = false): SaloonResponse
    {
        // 🚀 ... 🌑 ... 💫

        $mockClient ??= ($this->getMockClient() ?? $this->getConnector()->getMockClient());

        return $this->getRequestManager($mockClient, $asynchronous)->send();
    }

    /**
     * Send the request asynchronously
     *
     * @param MockClient|null $mockClient
     * @return PromiseInterface
     * @throws SaloonException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    public function sendAsync(MockClient $mockClient = null): PromiseInterface
    {
        return $this->getRequestManager($mockClient, true)->send();
    }

    /**
     * Create a request manager instance from the request.
     *
     * @param MockClient|null $mockClient
     * @param bool $asynchronous
     * @return RequestManager
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    public function getRequestManager(MockClient $mockClient = null, bool $asynchronous = false): RequestManager
    {
        return new RequestManager($this, $mockClient, $asynchronous);
    }
}
