<?php declare(strict_types=1);

namespace Saloon\Http\Senders;

use Exception;
use GuzzleHttp\Utils;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Saloon\Contracts\Sender;
use GuzzleHttp\RequestOptions;
use Saloon\Http\Responses\Response;
use Saloon\Contracts\PendingRequest;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Saloon\Exceptions\FatalRequestException;
use GuzzleHttp\Exception\BadResponseException;
use Saloon\Repositories\Body\FormBodyRepository;
use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Contracts\Response as ResponseContract;
use Saloon\Repositories\Body\StringBodyRepository;
use Saloon\Repositories\Body\MultipartBodyRepository;

class GuzzleSender implements Sender
{
    /**
     * The Guzzle client.
     *
     * @var GuzzleClient
     */
    protected GuzzleClient $client;

    /**
     * Guzzle's Handler Stack.
     *
     * @var HandlerStack
     */
    protected HandlerStack $handlerStack;

    /**
     * Constructor
     *
     * Create the HTTP client.
     */
    public function __construct()
    {
        $this->client = $this->createGuzzleClient();
    }

    /**
     * Get the sender's response class
     *
     * @return string
     */
    public function getResponseClass(): string
    {
        return Response::class;
    }

    /**
     * Create a new Guzzle client
     *
     * @return GuzzleClient
     */
    protected function createGuzzleClient(): GuzzleClient
    {
        $this->handlerStack = $this->createHandlerStack();

        $clientConfig = [
            'connect_timeout' => 10,
            'timeout' => 30,
            'http_errors' => false,
            'handler' => $this->handlerStack,
        ];

        return new GuzzleClient($clientConfig);
    }

    /**
     * Create a blank handler stack.
     *
     * @return HandlerStack
     */
    protected function createHandlerStack(): HandlerStack
    {
        $stack = new HandlerStack();
        $stack->setHandler(Utils::chooseHandler());

        return $stack;
    }

    /**
     * Send a request
     *
     * @param PendingRequest $pendingRequest
     * @param bool $asynchronous
     * @return ResponseContract|PromiseInterface
     * @throws GuzzleException
     */
    public function sendRequest(PendingRequest $pendingRequest, bool $asynchronous = false): ResponseContract|PromiseInterface
    {
        return $asynchronous === true
            ? $this->sendAsynchronousRequest($pendingRequest)
            : $this->sendSynchronousRequest($pendingRequest);
    }

    /**
     * Send a synchronous request.
     *
     * @param PendingRequest $pendingRequest
     * @return ResponseContract
     * @throws GuzzleException
     */
    protected function sendSynchronousRequest(PendingRequest $pendingRequest): ResponseContract
    {
        $guzzleRequest = $this->createGuzzleRequest($pendingRequest);
        $guzzleRequestOptions = $this->createRequestOptions($pendingRequest);

        try {
            $guzzleResponse = $this->client->send($guzzleRequest, $guzzleRequestOptions);
        } catch (BadResponseException $exception) {
            return $this->createResponse($pendingRequest, $exception->getResponse(), $exception);
        }

        return $this->createResponse($pendingRequest, $guzzleResponse);
    }

    /**
     * Send an asynchronous request
     *
     * @param PendingRequest $pendingRequest
     * @return PromiseInterface
     */
    protected function sendAsynchronousRequest(PendingRequest $pendingRequest): PromiseInterface
    {
        $guzzleRequest = $this->createGuzzleRequest($pendingRequest);
        $guzzleRequestOptions = $this->createRequestOptions($pendingRequest);

        $promise = $this->client->sendAsync($guzzleRequest, $guzzleRequestOptions);

        return $this->processPromise($promise, $pendingRequest);
    }

    /**
     * Create the Guzzle request
     *
     * @param PendingRequest $pendingRequest
     * @return Request
     */
    protected function createGuzzleRequest(PendingRequest $pendingRequest): Request
    {
        return new Request($pendingRequest->getMethod()->value, $pendingRequest->getUrl());
    }

    /**
     * Build up all the request options
     *
     * @param PendingRequest $pendingRequest
     * @return array
     */
    protected function createRequestOptions(PendingRequest $pendingRequest): array
    {
        $requestOptions = [];

        if ($pendingRequest->headers()->isNotEmpty()) {
            $requestOptions[RequestOptions::HEADERS] = $pendingRequest->headers()->all();
        }

        if ($pendingRequest->queryParameters()->isNotEmpty()) {
            $requestOptions[RequestOptions::QUERY] = $pendingRequest->queryParameters()->all();
        }

        foreach ($pendingRequest->config()->all() as $configVariable => $value) {
            $requestOptions[$configVariable] = $value;
        }

        $body = $pendingRequest->body();

        if (is_null($body) || $body->isEmpty()) {
            return $requestOptions;
        }

        match (true) {
            $body instanceof JsonBodyRepository => $requestOptions['json'] = $body->all(),
            $body instanceof MultipartBodyRepository => $requestOptions['multipart'] = $body->all(),
            $body instanceof FormBodyRepository => $requestOptions['form_params'] = $body->all(),
            $body instanceof StringBodyRepository => $requestOptions['body'] = $body->all(),
            default => $requestOptions['body'] = (string)$body,
        };

        return $requestOptions;
    }

    /**
     * Create a response.
     *
     * @param PendingRequest $pendingSaloonRequest
     * @param ResponseInterface $guzzleResponse
     * @param Exception|null $exception
     * @return ResponseContract
     */
    protected function createResponse(PendingRequest $pendingSaloonRequest, ResponseInterface $guzzleResponse, Exception $exception = null): ResponseContract
    {
        $responseClass = $pendingSaloonRequest->getResponseClass();

        return new $responseClass($pendingSaloonRequest, $guzzleResponse, $exception);
    }

    /**
     * Update the promise provided by Guzzle.
     *
     * @param PromiseInterface $promise
     * @param PendingRequest $pendingRequest
     * @return PromiseInterface
     */
    protected function processPromise(PromiseInterface $promise, PendingRequest $pendingRequest): PromiseInterface
    {
        return $promise
            ->then(
                function (ResponseInterface $guzzleResponse) use ($pendingRequest) {
                    // Instead of the promise returning a Guzzle response, we want to return
                    // a Saloon response.

                    return $this->createResponse($pendingRequest, $guzzleResponse);
                },
                function (GuzzleException $guzzleException) use ($pendingRequest) {
                    // If the exception was a connect exception, we should return that in the
                    // promise instead rather than trying to convert it into a
                    // Response, since there was no response.

                    if (! $guzzleException instanceof RequestException) {
                        throw new FatalRequestException($guzzleException, $pendingRequest);
                    }

                    $response = $this->createResponse($pendingRequest, $guzzleException->getResponse(), $guzzleException);

                    throw $response->toException();
                }
            );
    }

    /**
     * Add a middleware to the handler stack.
     *
     * @param callable $callable
     * @param string $name
     * @return $this
     */
    public function addMiddleware(callable $callable, string $name = ''): static
    {
        $this->handlerStack->push($callable, $name);

        return $this;
    }

    /**
     * Overwrite the entire handler stack.
     *
     * @param HandlerStack $handlerStack
     * @return $this
     */
    public function setHandlerStack(HandlerStack $handlerStack): static
    {
        $this->handlerStack = $handlerStack;

        return $this;
    }

    /**
     * Get the handler stack.
     *
     * @return HandlerStack
     */
    public function getHandlerStack(): HandlerStack
    {
        return $this->handlerStack;
    }

    /**
     * Get the Guzzle client
     *
     * @return GuzzleClient
     */
    public function getGuzzleClient(): GuzzleClient
    {
        return $this->client;
    }
}
