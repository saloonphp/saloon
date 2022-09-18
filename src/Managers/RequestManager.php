<?php

namespace Sammyjo20\Saloon\Managers;

use Exception;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\SaloonRequest;
use GuzzleHttp\Exception\GuzzleException;
use Sammyjo20\Saloon\Http\SaloonResponse;
use GuzzleHttp\Exception\RequestException;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Traits\ManagesGuzzle;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\ManagesPlugins;
use Sammyjo20\Saloon\Clients\BaseMockClient;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Sammyjo20\Saloon\Traits\CollectsHandlers;
use GuzzleHttp\Exception\BadResponseException;
use Sammyjo20\Saloon\Traits\CollectsQueryParams;
use Sammyjo20\Saloon\Traits\CollectsInterceptors;
use Sammyjo20\Saloon\Interfaces\AuthenticatorInterface;
use Sammyjo20\Saloon\Exceptions\SaloonMultipleMockMethodsException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponsesProvidedException;

class RequestManager
{
    use ManagesGuzzle,
        ManagesPlugins,
        CollectsHeaders,
        CollectsConfig,
        CollectsQueryParams,
        CollectsHandlers,
        CollectsInterceptors;

    /**
     * The request that we are about to dispatch.
     *
     * @var SaloonRequest
     */
    private SaloonRequest $request;

    /**
     * The Saloon connector.
     *
     * @var SaloonConnector
     */
    private SaloonConnector $connector;

    /**
     * Are we running Saloon in a Laravel environment?
     *
     * @var bool
     */
    public bool $inLaravelEnvironment = false;

    /**
     * The Laravel manager
     *
     * @var LaravelManager|null
     */
    protected ?LaravelManager $laravelManger = null;

    /**
     * The mock client if it has been provided
     *
     * @var BaseMockClient|null
     */
    protected ?BaseMockClient $mockClient = null;

    /**
     * Determines if the request should be sent asynchronously.
     *
     * @var bool
     */
    protected bool $asynchronous = false;

    /**
     * Construct the request manager
     *
     * @param SaloonRequest $request
     * @param MockClient|null $mockClient
     * @param bool $asynchronous
     * @throws SaloonMultipleMockMethodsException
     * @throws SaloonNoMockResponsesProvidedException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function __construct(SaloonRequest $request, MockClient $mockClient = null, bool $asynchronous = false)
    {
        $this->request = $request;
        $this->connector = $request->getConnector();
        $this->inLaravelEnvironment = $this->detectLaravel();
        $this->asynchronous = $asynchronous;

        $this->bootLaravelManager();
        $this->bootMockClient($mockClient);
    }

    /**
     * Hydrate the request manager
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function hydrate(): void
    {
        // Load up any plugins and if they add any headers, then we add them to the array.
        // Some plugins, like the "hasBody" plugin, will need some manual code.

        $this->loadPlugins();

        // Run the boot methods of the connector and requests these are only used to add
        // extra functionality at a pinch.

        $this->connector->boot($this->request);
        $this->request->boot($this->request);

        // Now let's run our authenticator if one is present.

        $this->authenticateRequest();

        // Merge in response interceptors now

        $this->mergeResponseInterceptors($this->connector->getResponseInterceptors(), $this->request->getResponseInterceptors());

        // Merge the headers, query, and config (request always takes presidency).

        $this->mergeHeaders($this->connector->getHeaders(), $this->request->getHeaders());

        // Merge in query params

        $this->mergeQuery($this->connector->getQuery(), $this->request->getQuery());

        // Merge the config

        $this->mergeConfig($this->connector->getConfig(), $this->request->getConfig());

        // Add the query parameters to the config

        $query = $this->getQuery();

        if (! empty($query)) {
            $this->mergeConfig(['query' => $query]);
        }

        // Merge in any handlers

        $this->mergeHandlers($this->connector->getHandlers(), $this->request->getHandlers());

        // Now we'll merge in anything added by Laravel.

        if ($this->laravelManger instanceof LaravelManager) {
            $this->mergeResponseInterceptors($this->laravelManger->getResponseInterceptors());
            $this->mergeHeaders($this->laravelManger->getHeaders());
            $this->mergeConfig($this->laravelManger->getConfig());
            $this->mergeHandlers($this->laravelManger->getHandlers());
        }
    }

    /**
     * Send off the request 🚀
     *
     * @return SaloonResponse|PromiseInterface
     * @throws SaloonInvalidResponseClassException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonDuplicateHandlerException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidHandlerException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonMissingMockException
     */
    public function send()
    {
        // Let's firstly hydrate the request manager, which will retrieve all the attributes
        // from the request and connector and build them up inside the request.

        $this->hydrate();

        // Next, we will retrieve our Guzzle client, request and build up the request options
        // in a way Guzzle will understand.

        $client = $this->createGuzzleClient();
        $request = $this->createGuzzleRequest();
        $requestOptions = $this->buildRequestOptions();

        // Finally, we will send the request! If the asynchronous mode has been requested,
        // we will return a promise with the Saloon response, however if not then we will
        // just return a response.

        return $this->asynchronous === true
            ? $this->sendAsyncRequest($client, $request, $requestOptions)
            : $this->sendSyncRequest($client, $request, $requestOptions);
    }

    /**
     * Send a traditional, synchronous request.
     *
     * @param GuzzleClient $client
     * @param GuzzleRequest $request
     * @param array $requestOptions
     * @return SaloonResponse
     * @throws SaloonInvalidResponseClassException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    private function sendSyncRequest(GuzzleClient $client, GuzzleRequest $request, array $requestOptions): SaloonResponse
    {
        try {
            $guzzleResponse = $client->send($request, $requestOptions);
        } catch (BadResponseException $exception) {
            return $this->createResponse($requestOptions, $exception->getResponse(), $exception);
        }

        return $this->createResponse($requestOptions, $guzzleResponse);
    }

    /**
     * Prepare an asynchronous request, and return a promise.
     *
     * @param GuzzleClient $client
     * @param GuzzleRequest $request
     * @param array $requestOptions
     * @return PromiseInterface
     */
    private function sendAsyncRequest(GuzzleClient $client, GuzzleRequest $request, array $requestOptions): PromiseInterface
    {
        return $client->sendAsync($request, $requestOptions)
            ->then(
                function (ResponseInterface $guzzleResponse) use ($requestOptions) {
                    // Instead of the promise returning a Guzzle response, we want to return
                    // a Saloon response.

                    return $this->createResponse($requestOptions, $guzzleResponse);
                },
                function (GuzzleException $guzzleException) use ($requestOptions) {
                    // If the exception was a connect exception, we should return that in the
                    // promise instead rather than trying to convert it into a
                    // SaloonResponse, since there was no response.

                    if (! $guzzleException instanceof RequestException) {
                        throw $guzzleException;
                    }

                    $response = $this->createResponse($requestOptions, $guzzleException->getResponse(), $guzzleException);

                    throw $response->toException();
                }
            );
    }

    /**
     * Create a response.
     *
     * @param array $requestOptions
     * @param Response $response
     * @param RequestException|null $exception
     * @return SaloonResponse
     * @throws SaloonInvalidResponseClassException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    private function createResponse(array $requestOptions, Response $response, RequestException $exception = null): SaloonResponse
    {
        $request = $this->request;
        $responseClass = $request->getResponseClass();

        /** @var SaloonResponse $response */
        $response = new $responseClass($requestOptions, $request, $response, $exception);

        // If we are mocking, we should record the request and response on the mock manager,
        // so we can run assertions on the responses.

        if ($this->isMocking()) {
            $response->setMocked(true);
            $this->mockClient->recordResponse($response);
        }

        // Run Response Interceptors

        foreach ($this->getResponseInterceptors() as $responseInterceptor) {
            $response = $responseInterceptor($request, $response);
        }

        return $response;
    }

    /**
     * Check if we can detect Laravel
     *
     * @return bool
     */
    private function detectLaravel(): bool
    {
        try {
            return function_exists('resolve') && resolve('saloon') instanceof \Sammyjo20\SaloonLaravel\Saloon;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     *  Retrieve the Laravel manager from the Laravel package.
     *
     * @return void
     */
    private function bootLaravelManager(): void
    {
        // If we're not running Laravel, just stop here.

        if ($this->inLaravelEnvironment === false) {
            return;
        }

        // If we can detect Laravel, let's run the internal Laravel resolve method to import
        // our facade and boot it up. This will return any added

        $laravelManager = resolve('saloon')->bootLaravelFeatures(new LaravelManager, $this->request);

        $this->laravelManger = $laravelManager;
        $this->mockClient = $laravelManager->getMockClient();
    }

    /**
     * Boot the mock client
     *
     * @param MockClient|null $mockClient
     * @return void
     * @throws SaloonMultipleMockMethodsException
     * @throws SaloonNoMockResponsesProvidedException
     */
    private function bootMockClient(MockClient|null $mockClient): void
    {
        if (is_null($mockClient)) {
            return;
        }

        if ($mockClient->isEmpty()) {
            throw new SaloonNoMockResponsesProvidedException;
        }

        if ($this->isMocking()) {
            throw new SaloonMultipleMockMethodsException;
        }

        $this->mockClient = $mockClient;
    }

    /**
     * Build up all the request options
     *
     * @return array
     */
    private function buildRequestOptions(): array
    {
        $requestOptions = [
            RequestOptions::HEADERS => $this->getHeaders(),
        ];

        foreach ($this->getConfig() as $configVariable => $value) {
            $requestOptions[$configVariable] = $value;
        }

        return $requestOptions;
    }

    /**
     * Is the manager in mocking mode?
     *
     * @return bool
     */
    public function isMocking(): bool
    {
        return $this->mockClient instanceof BaseMockClient;
    }

    /**
     * Retrieve the request from the request manager.
     *
     * @return SaloonRequest
     */
    public function getRequest(): SaloonRequest
    {
        return $this->request;
    }

    /**
     * Authenticate the request by running the authenticator if it is present.
     *
     * @return void
     */
    private function authenticateRequest(): void
    {
        $authenticator = $this->request->getAuthenticator() ?? $this->connector->getAuthenticator();

        if (! $authenticator instanceof AuthenticatorInterface) {
            return;
        }

        $authenticator->set($this->request);
    }
}
