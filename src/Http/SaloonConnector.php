<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Traits\Bootable;
use Sammyjo20\Saloon\Traits\HasSender;
use Sammyjo20\Saloon\Traits\MocksRequests;
use Sammyjo20\Saloon\Traits\SendsRequests;
use Sammyjo20\Saloon\Traits\ProxiesRequests;
use Sammyjo20\Saloon\Http\Senders\GuzzleSender;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Traits\HasRequestProperties;
use Sammyjo20\Saloon\Traits\AuthenticatesRequests;
use Sammyjo20\Saloon\Exceptions\ClassNotFoundException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidRequestException;
use Sammyjo20\Saloon\Exceptions\SaloonConnectorMethodNotFoundException;

/**
 * @method GuzzleSender sender()
 */
abstract class SaloonConnector
{
    use AuthenticatesRequests;
    use HasRequestProperties;
    use HasCustomResponses;
    use ProxiesRequests;
    use MocksRequests;
    use SendsRequests;
    use HasSender;
    use Bootable;

    /**
     * Define the base URL of the API.
     *
     * @return string
     */
    abstract public function defineBaseUrl(): string;

    /**
     * Prepare a new request by providing it the current instance of the connector.
     *
     * @param SaloonRequest $request
     * @return SaloonRequest
     */
    public function request(SaloonRequest $request): SaloonRequest
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
     * @return mixed
     * @throws ClassNotFoundException
     * @throws SaloonConnectorMethodNotFoundException
     * @throws SaloonInvalidRequestException
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
     * @return SaloonConnector
     */
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }
}
