<?php

namespace Sammyjo20\Saloon\Http;

use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Exceptions\ClassNotFoundException;
use Sammyjo20\Saloon\Exceptions\SaloonConnectorMethodNotFoundException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidRequestException;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;
use Sammyjo20\Saloon\Traits\AuthenticatesRequests;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Traits\HasRequestProperties;
use Sammyjo20\Saloon\Traits\HasRequestSender;
use Sammyjo20\Saloon\Traits\MocksRequests;
use Sammyjo20\Saloon\Traits\ProxiesRequests;
use Sammyjo20\Saloon\Traits\BundlesRequestProperties;

abstract class SaloonConnector
{
    use HasRequestProperties;
    use BundlesRequestProperties;
    use AuthenticatesRequests;
    use HasCustomResponses;
    use HasRequestSender;
    use ProxiesRequests;
    use MocksRequests;

    /**
     * Register Saloon requests that will become methods on the connector.
     * For example, GetUserRequest would become $this->getUserRequest(...$args)
     *
     * @var array|string[]
     */
    protected array $requests = [];

    /**
     * Define the base URL of the API.
     *
     * @return string
     */
    abstract protected function defineBaseUrl(): string;

    /**
     * @param PendingSaloonRequest $requestPayload
     * @return void
     */
    public function boot(PendingSaloonRequest $requestPayload): void
    {
        //
    }

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
     * Send a Saloon request with the current instance of the connector.
     *
     * @param SaloonRequest $request
     * @param MockClient|null $mockClient
     * @return SaloonResponse
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException
     */
    public function send(SaloonRequest $request, MockClient $mockClient = null): SaloonResponse
    {
        return $this->request($request)->send($mockClient);
    }

    /**
     * Send an asynchronous Saloon request with the current instance of the connector.
     *
     * @param SaloonRequest $request
     * @param MockClient|null $mockClient
     * @return PromiseInterface
     */
    public function sendAsync(SaloonRequest $request, MockClient $mockClient = null): PromiseInterface
    {
        return $this->request($request)->sendAsync($mockClient);
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
