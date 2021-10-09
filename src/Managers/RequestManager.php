<?php

namespace Sammyjo20\Saloon\Managers;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\ManagesFeatures;
use Sammyjo20\Saloon\Traits\ManagesGuzzle;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;

class RequestManager
{
    use ManagesGuzzle;
    use ManagesFeatures;

    use CollectsHeaders,
        CollectsConfig;

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
     * Check if the request manager is mocking.
     *
     * @var bool
     */
    public bool $isMocking = false;

    /**
     * Construct the request manager
     *
     * @param SaloonRequest $request
     * @param string|null $mockType
     * @throws \ReflectionException
     */
    public function __construct(SaloonRequest $request, string $mockType = null)
    {
        $this->request = $request;
        $this->connector = $request->getConnector();
        $this->isMocking = in_array($mockType, [Saloon::SUCCESS_MOCK, Saloon::FAILURE_MOCK], true);
        $this->mockType = $mockType;

        $this->bootManager();
    }

    /**
     * Boot up the request manager, merge the headers, query, and config.
     *
     * @throws \ReflectionException
     */
    private function bootManager(): void
    {
        $this->createGuzzleClient();

        // Load up any features and if they add any headers, then we add them to the array.
        // Some features, like the "hasBody" feature, will need some manual code.

        $this->loadFeatures();

        // Merge the headers, query, and config (request always takes presidency).

        $this->mergeHeaders($this->connector->getHeaders(), $this->request->getHeaders());

        // Merge the config

        $this->mergeConfig($this->connector->getConfig(), $this->request->getConfig());
    }

    public function send()
    {
        $request = $this->request;

        // Remove any leading slashes on the endpoint.

        $endpoint = ltrim($request->defineEndpoint(), '/ ');
        $guzzleRequest = new Request($request->getMethod(), $endpoint);

        // Run the interceptors.

        $guzzleRequest = $this->connector->interceptRequest($guzzleRequest);
        $guzzleRequest = $this->request->interceptRequest($guzzleRequest);

        // Build up the config!

        $requestOptions = [
            RequestOptions::HEADERS => $this->getHeaders(),
        ];

        // Recursively add config variables...

        foreach ($this->getConfig() as $configVariable => $value) {
            $requestOptions[$configVariable] = $value;
        }

        // Send the request! 🚀

        try {
            $guzzleResponse = $this->guzzleClient->send($guzzleRequest, $requestOptions);
        } catch (GuzzleException $exception) {
            // Todo: Catch ClientExceptions separately
            return $this->createRepsonse($requestOptions, $exception->getRequest(), $exception->getResponse());
        }

        return $this->createRepsonse($requestOptions, $guzzleRequest, $guzzleResponse);
    }

    /**
     * Create a response.
     *
     * @param array $requestOptions
     * @param Request $request
     * @param Response $response
     * @return SaloonResponse
     */
    private function createRepsonse(array $requestOptions, Request $request, Response $response): SaloonResponse
    {
        $shouldGuessStatusFromBody = isset($this->connector->shouldGuessStatusFromBody) || isset($this->request->shouldGuessStatusFromBody);

        $requestiResponse =  new SaloonResponse($requestOptions, $response, $shouldGuessStatusFromBody);

        $requestiResponse = $this->connector->interceptResponse($request, $requestiResponse);
        $requestiResponse = $this->request->interceptResponse($request, $requestiResponse);

        // Todo: Create a nice fresh standardised response class on top of guzzles

        return $requestiResponse;
    }
}
