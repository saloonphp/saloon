<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Managers\RequestManager;

trait SendsRequests
{
    /**
     * Send the request.
     *
     * @param MockClient|null $mockClient
     * @return SaloonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    public function send(MockClient $mockClient = null): SaloonResponse
    {
        // 🚀 ... 🌑 ... 💫

        return $this->getRequestManager($mockClient)->send();
    }

    /**
     * Create a request manager instance from the request.
     *
     * @param MockClient|null $mockClient
     * @return RequestManager
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonMultipleMockMethodsException
     */
    public function getRequestManager(MockClient $mockClient = null): RequestManager
    {
        return new RequestManager($this, $mockClient);
    }
}
