<?php

namespace Sammyjo20\Saloon\Managers;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Composer\InstalledVersions;
use Sammyjo20\Saloon\Http\SaloonRequest;
use GuzzleHttp\Exception\GuzzleException;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Traits\ManagesGuzzle;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\ManagesFeatures;
use Sammyjo20\Saloon\Traits\CollectsHandlers;
use GuzzleHttp\Exception\BadResponseException;
use Sammyjo20\Saloon\Traits\CollectsInterceptors;

class RequestManager
{
    use ManagesGuzzle,
        ManagesFeatures,
        CollectsHeaders,
        CollectsConfig,
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
    protected bool $inLaravelEnvironment = false;

    /**
     * The Laravel manager
     *
     * @var LaravelManger
     */
    protected LaravelManger $laravelManger;

    /**
     * Construct the request manager
     *
     * @param SaloonRequest $request
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function __construct(SaloonRequest $request)
    {
        $this->request = $request;
        $this->connector = $request->getConnector();
        $this->inLaravelEnvironment = $this->detectLaravel();
        $this->laravelManger = new LaravelManger;
    }

    /**
     * Hydrate the request manager
     *
     * @return void
     * @throws \ReflectionException
     */
    public function hydrate(): void
    {
        // Load up any features and if they add any headers, then we add them to the array.
        // Some features, like the "hasBody" feature, will need some manual code.

        $this->loadFeatures();

        // Run the boot methods of the connector and requests these are only used to add
        // extra functionality at a pinch.

        $this->connector->boot();
        $this->request->boot();

        // Merge in response interceptors now

        $this->mergeResponseInterceptors($this->connector->getResponseInterceptors(), $this->request->getResponseInterceptors());

        // Merge the headers, query, and config (request always takes presidency).

        $this->mergeHeaders($this->connector->getHeaders(), $this->request->getHeaders());

        // Merge the config

        $this->mergeConfig($this->connector->getConfig(), $this->request->getConfig());

        // Merge in any handlers

        $this->mergeHandlers($this->connector->getHandlers(), $this->request->getHandlers());

        // If we're not running Laravel, just stop here.

        if ($this->inLaravelEnvironment === false) {
            return;
        }

        // If we can detect Laravel, let's run the internal Laravel resolve method to import
        // our facade and boot it up. This will return any added

        $laravelManager = $this->laravelManger;

        $laravelManager = resolve('saloon')->boot($laravelManager);

        // Now we'll merge in anything added by Laravel.

        $this->mergeResponseInterceptors($laravelManager->getResponseInterceptors());
        $this->mergeHeaders($laravelManager->getHeaders());
        $this->mergeConfig($laravelManager->getConfig());
        $this->mergeHandlers($laravelManager->getHandlers());
    }

    /**
     * Send off the message... 🚀
     *
     * @return SaloonResponse
     * @throws GuzzleException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonDuplicateHandlerException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidHandlerException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonMissingMockException
     */
    public function send()
    {
        // Hydrate the manager with juicy headers, config, interceptors, handlers...

        $this->hydrate();

        // Build up the config!

        $requestOptions = [
            RequestOptions::HEADERS => $this->getHeaders(),
        ];

        // Recursively add config variables...

        foreach ($this->getConfig() as $configVariable => $value) {
            $requestOptions[$configVariable] = $value;
        }

        // Boot up our Guzzle client... This will also boot up handlers...

        $client = $this->createGuzzleClient();

        // Send the request! 🚀

        try {
            $guzzleResponse = $client->send($this->createGuzzleRequest(), $requestOptions);
        } catch (BadResponseException $exception) {
            return $this->createResponse($requestOptions, $exception->getResponse());
        }

        return $this->createResponse($requestOptions, $guzzleResponse);
    }

    /**
     * Create a response.
     *
     * @param array $requestOptions
     * @param Response $response
     * @return SaloonResponse
     */
    private function createResponse(array $requestOptions, Response $response): SaloonResponse
    {
        $request = $this->request;

        $shouldGuessStatusFromBody = isset($this->connector->shouldGuessStatusFromBody) || isset($this->request->shouldGuessStatusFromBody);

        $response = new SaloonResponse($requestOptions, $request, $response, $shouldGuessStatusFromBody);

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
        $hasRequiredDependencies = InstalledVersions::isInstalled('laravel/framework') && InstalledVersions::isInstalled('sammyjo20/saloon-laravel');

        try {
            return $hasRequiredDependencies && resolve('saloon') instanceof \Sammyjo20\SaloonLaravel\Saloon;
        } catch (\Exception $ex) {
            return false;
        }
    }
}
