<?php declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Traits\Bootable;
use Saloon\Traits\HasMockClient;
use Saloon\Traits\Connector\HasPool;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Traits\Connector\HasSender;
use Saloon\Traits\Connector\SendsRequests;
use Saloon\Traits\Connector\ProxiesRequests;
use Saloon\Exceptions\ClassNotFoundException;
use Saloon\Traits\Auth\AuthenticatesRequests;
use Saloon\Traits\Responses\HasCustomResponses;
use Saloon\Exceptions\InvalidRequestException;
use Saloon\Traits\RequestProperties\HasRequestProperties;
use Saloon\Exceptions\ConnectorMethodNotFoundException;

/**
 * @method GuzzleSender sender()
 */
abstract class Connector
{
    use AuthenticatesRequests;
    use HasRequestProperties;
    use HasCustomResponses;
    use ProxiesRequests;
    use HasMockClient;
    use SendsRequests;
    use HasSender;
    use Bootable;
    use HasPool;

    /**
     * Define the base URL of the API.
     *
     * @return string
     */
    abstract public function defineBaseUrl(): string;

    /**
     * Prepare a new request by providing it the current instance of the connector.
     *
     * @param Request $request
     * @return Request
     */
    public function request(Request $request): Request
    {
        return $request->setConnector($this);
    }

    /**
     * Dynamically proxy other methods to try and call a requests.
     *
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws ClassNotFoundException
     * @throws ConnectorMethodNotFoundException
     * @throws InvalidRequestException
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
     * @return mixed
     * @throws ClassNotFoundException
     * @throws ConnectorMethodNotFoundException
     * @throws InvalidRequestException
     * @throws \ReflectionException
     */
    public static function __callStatic($method, $arguments)
    {
        return (new static)->proxyRequest($method, $arguments);
    }

    /**
     * Instantiate a new class with the arguments.
     *
     * @param mixed ...$arguments
     * @return Connector
     */
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }
}
