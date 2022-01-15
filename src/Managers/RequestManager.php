<?php

namespace Sammyjo20\Saloon\Managers;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use GuzzleHttp\Exception\GuzzleException;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Traits\ManagesGuzzle;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\ManagesFeatures;

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
    }

    /**
     * Hydrate the request manager
     *
     * @return void
     * @throws \ReflectionException
     */
    private function hydrateManager(): void
    {
        // Load up any features and if they add any headers, then we add them to the array.
        // Some features, like the "hasBody" feature, will need some manual code.

        $this->loadFeatures();

        // Merge the headers, query, and config (request always takes presidency).

        $this->mergeHeaders($this->connector->getHeaders(), $this->request->getHeaders());

        // Merge the config

        $this->mergeConfig($this->connector->getConfig(), $this->request->getConfig());
    }

    /**
     * Prepare the request manager for message shipment
     *
     * @return void
     * @throws \ReflectionException
     */
    public function prepareMessage(): SaloonRequest
    {
        $request = &$this->request;

        // Run the interceptors.

        $request = $this->connector->interceptRequest($request);
        $request = $this->request->interceptRequest($request);

        // Rehydrate the manager

        $this->hydrateManager();

        return $request;
    }

    /**
     * Send off the message... 🚀
     *
     * @return SaloonResponse
     * @throws GuzzleException
     * @throws \ReflectionException
     */
    public function send()
    {
        $request = $this->prepareMessage();

        // Remove any leading slashes on the endpoint.

        $endpoint = ltrim($request->defineEndpoint(), '/ ');

        $guzzleRequest = new Request($request->getMethod(), $endpoint);

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
        } catch (BadResponseException $exception) {
            return $this->createResponse($requestOptions, $request, $exception->getResponse());
        }

        return $this->createResponse($requestOptions, $request, $guzzleResponse);
    }

    /**
     * Create a response.
     *
     * @param array $requestOptions
     * @param SaloonRequest $request
     * @param Response $response
     * @return SaloonResponse
     */
    private function createResponse(array $requestOptions, SaloonRequest $request, Response $response): SaloonResponse
    {
        $shouldGuessStatusFromBody = isset($this->connector->shouldGuessStatusFromBody) || isset($this->request->shouldGuessStatusFromBody);

        $response = new SaloonResponse($requestOptions, $request, $response, $shouldGuessStatusFromBody);

        $response = $this->connector->interceptResponse($request, $response);
        $response = $this->request->interceptResponse($request, $response);

        return $response;
    }
}
