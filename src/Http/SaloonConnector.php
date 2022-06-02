<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Exceptions\ClassNotFoundException;
use Sammyjo20\Saloon\Exceptions\SaloonConnectorMethodNotFoundException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidRequestException;
use Sammyjo20\Saloon\Interfaces\SaloonConnectorInterface;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Traits\HasRequestProperties;
use Sammyjo20\Saloon\Traits\MocksRequests;
use Sammyjo20\Saloon\Traits\ProxiesRequests;

abstract class SaloonConnector
{
    use HasRequestProperties;
    use HasCustomResponses;
    use MocksRequests;
    use ProxiesRequests;

    abstract protected function defineBaseUrl(): string;

    /**
     * @param PendingSaloonRequest $requestPayload
     * @return void
     */
    public function beforeSend(PendingSaloonRequest $requestPayload): void
    {
        //
    }

    /**
     * Dynamically proxy other methods to try and call a requests.
     *
     * @param $method
     * @param $arguments
     * @return AnonymousRequestCollection|SaloonRequest
     * @throws ClassNotFoundException
     * @throws SaloonConnectorMethodNotFoundException
     * @throws SaloonInvalidRequestException
     * @throws \ReflectionException
     */
    public function __call($method, $arguments)
    {
        return $this->proxyRequest($method, $arguments);
    }

    /**
     * Dynamically proxy other methods to try and call a requests.
     *
     * @param $method
     * @param $arguments
     * @return SaloonRequest
     * @throws ClassNotFoundException
     * @throws SaloonInvalidRequestException
     * @throws SaloonConnectorMethodNotFoundException
     * @throws \ReflectionException
     */
    public static function __callStatic($method, $arguments)
    {
        return (new static)->proxyRequest($method, $arguments);
    }
}
