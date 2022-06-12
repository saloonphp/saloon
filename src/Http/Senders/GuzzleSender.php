<?php

namespace Sammyjo20\Saloon\Http\Senders;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Sammyjo20\Saloon\Data\RequestDataType;
use GuzzleHttp\Client as GuzzleClient;
use Sammyjo20\Saloon\Http\RequestSender;
use Sammyjo20\Saloon\Http\SaloonResponse;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\BadResponseException;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Interfaces\RequestSenderInterface;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException;

class GuzzleSender extends RequestSender
{
    /**
     * @param PendingSaloonRequest $request
     * @return SaloonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
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
            RequestDataType::JSON => $requestOptions['json'] = $data,
            RequestDataType::MULTIPART => $requestOptions['multipart'] = $data,
            RequestDataType::FORM => $requestOptions['form_params'] = $data,
            RequestDataType::MIXED => $requestOptions['body'] = $data,
            default => null,
        };

        return $requestOptions;
    }

    /**
     * Create a response.
     *
     * @param PendingSaloonRequest $pendingSaloonRequest
     * @param Response $guzzleResponse
     * @param RequestException|null $exception
     * @return SaloonResponse
     */
    private function createResponse(PendingSaloonRequest $pendingSaloonRequest, Response $guzzleResponse, RequestException $exception = null): SaloonResponse
    {
        $responseClass = $pendingSaloonRequest->getResponseClass();

        /** @var SaloonResponse $response */
        $response = new $responseClass($pendingSaloonRequest, $guzzleResponse, $exception);

        // Run the response pipeline

        $pendingSaloonRequest->executeResponsePipeline($response);

        // If we are mocking, we should record the request and response on the mock manager,
        // so we can run assertions on the responses.
//
//        if ($this->isMocking()) {
//            $response->setMocked(true);
//            $this->mockClient->recordResponse($response);
//        }

        return $response;
    }
}
