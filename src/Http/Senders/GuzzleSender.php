<?php

namespace Sammyjo20\Saloon\Http\Senders;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Sammyjo20\Saloon\Data\DataType;
use Sammyjo20\Saloon\Exceptions\SaloonDuplicateHandlerException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidHandlerException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Interfaces\RequestSenderInterface;

class GuzzleSender implements RequestSenderInterface
{
    public function handle(PendingSaloonRequest $request): SaloonResponse
    {
        $client = $this->createGuzzleClient();
        $guzzleRequest = $this->createGuzzleRequest($request);
        $options = $this->createRequestOptions($request);

        try {
            $guzzleResponse = $client->send($guzzleRequest, $options);
        } catch (BadResponseException $exception) {
            return $this->createResponse($request, $exception->getResponse(), $exception);
        }

        return $this->createResponse($request, $guzzleResponse);
    }

    /**
     * Create a new Guzzle client
     *
     * @return GuzzleClient
     */
    private function createGuzzleClient(): GuzzleClient
    {
        return new GuzzleClient([
            'connect_timeout' => 10,
            'timeout' => 30,
            'http_errors' => true,
        ]);
    }

    /**
     * Create the Guzzle request
     *
     * @param PendingSaloonRequest $request
     * @return Request
     */
    private function createGuzzleRequest(PendingSaloonRequest $request): Request
    {
        return new Request($request->getMethod()->value, $request->getUrl());
    }

    /**
     * Build up all the request options
     *
     * @param PendingSaloonRequest $request
     * @return array
     */
    private function createRequestOptions(PendingSaloonRequest $request): array
    {
        $requestOptions = [
            RequestOptions::HEADERS => $request->headers()->all(),
        ];

        foreach ($request->config()->all() as $configVariable => $value) {
            $requestOptions[$configVariable] = $value;
        }

        // Build up the data options

        $data = $request->data()->all();

        match ($request->getDataType()) {
            DataType::JSON => $requestOptions['json'] = $data,
            DataType::MULTIPART => $requestOptions['multipart'] = $data,
            DataType::FORM => $requestOptions['form_params'] = $data,
            DataType::MIXED => $requestOptions['body'] = $data,
            default => null,
        };

        return $requestOptions;
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
    private function createResponse(PendingSaloonRequest $pendingSaloonRequest, Response $response, RequestException $exception = null): SaloonResponse
    {
        $request = $pendingSaloonRequest->getRequest();
        $responseClass = $pendingSaloonRequest->getResponseClass();

        /** @var SaloonResponse $response */
        $response = new $responseClass($pendingSaloonRequest, $request, $response, $exception);

        // Run the response pipeline

        $response = $pendingSaloonRequest->executeResponsePipeline($response);

        // If we are mocking, we should record the request and response on the mock manager,
        // so we can run assertions on the responses.
//
//        if ($this->isMocking()) {
//            $response->setMocked(true);
//            $this->mockClient->recordResponse($response);
//        }

        // Run Response Interceptors

//        foreach ($this->getResponseInterceptors() as $responseInterceptor) {
//            $response = $responseInterceptor($request, $response);
//        }

        return $response;
    }
}
