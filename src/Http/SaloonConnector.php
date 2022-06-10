<?php

namespace Sammyjo20\Saloon\Http;

use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Traits\CollectsData;
use Sammyjo20\Saloon\Traits\MocksRequests;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\GuessesRequests;
use Sammyjo20\Saloon\Traits\CollectsHandlers;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Traits\CollectsQueryParams;
use Sammyjo20\Saloon\Traits\CollectsInterceptors;
use Sammyjo20\Saloon\Traits\AuthenticatesRequests;
use Sammyjo20\Saloon\Exceptions\ClassNotFoundException;
use Sammyjo20\Saloon\Interfaces\SaloonConnectorInterface;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidRequestException;
use Sammyjo20\Saloon\Exceptions\SaloonConnectorMethodNotFoundException;

abstract class SaloonConnector implements SaloonConnectorInterface
{
    use CollectsHeaders;
    use CollectsData;
    use CollectsQueryParams;
    use CollectsConfig;
    use CollectsHandlers;
    use CollectsInterceptors;
    use AuthenticatesRequests;
    use HasCustomResponses;
    use MocksRequests;
    use GuessesRequests;

    /**
     * Register Saloon requests that will become methods on the connector.
     * For example, GetUserRequest would become $this->getUserRequest(...$args)
     *
     * @var array|string[]
     */
    protected array $requests = [];

    /**
     * Define anything that should be added to any requests
     * with this connector before the request is sent.
     *
     * @param SaloonRequest $request
     * @return void
     */
    public function boot(SaloonRequest $request): void
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
     * @throws \ReflectionException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
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
     * @return AnonymousRequestCollection|SaloonRequest
     * @throws ClassNotFoundException
     * @throws SaloonConnectorMethodNotFoundException
     * @throws SaloonInvalidRequestException
     * @throws \ReflectionException
     */
    public function __call($method, $arguments)
    {
        return $this->guessRequest($method, $arguments);
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
        return (new static)->guessRequest($method, $arguments);
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
