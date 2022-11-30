<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Enums\Method;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Http\Faking\SimulatedResponsePayload;

interface PendingRequest
{
    /**
     * Retrieve the authenticator.
     *
     * @return Authenticator|null
     */
    public function getAuthenticator(): ?Authenticator;

    /**
     * Authenticate the request with an authenticator.
     *
     * @param Authenticator $authenticator
     * @return \Saloon\Http\PendingRequest
     */
    public function authenticate(Authenticator $authenticator): static;

    /**
     * Authenticate the request with an Authorization header.
     *
     * @param string $token
     * @param string $prefix
     * @return \Saloon\Http\PendingRequest
     */
    public function withTokenAuth(string $token, string $prefix = 'Bearer'): static;

    /**
     * Authenticate the request with "basic" authentication.
     *
     * @param string $username
     * @param string $password
     * @return \Saloon\Http\PendingRequest
     */
    public function withBasicAuth(string $username, string $password): static;

    /**
     * Authenticate the request with "digest" authentication.
     *
     * @param string $username
     * @param string $password
     * @param string $digest
     * @return \Saloon\Http\PendingRequest
     */
    public function withDigestAuth(string $username, string $password, string $digest): static;

    /**
     * Authenticate the request with a query parameter token.
     *
     * @param string $parameter
     * @param string $value
     * @return \Saloon\Http\PendingRequest
     */
    public function withQueryAuth(string $parameter, string $value): static;

    /**
     * Invoke a callable where a given value returns a truthy value.
     *
     * @param mixed $value
     * @param callable $callback
     * @param callable|null $default
     * @return \Saloon\Http\PendingRequest
     */
    public function when(mixed $value, callable $callback, callable $default = null): static;

    /**
     * Invoke a callable when a given value returns a falsy value.
     *
     * @param mixed $value
     * @param callable $callback
     * @param mixed $default
     * @return \Saloon\Http\PendingRequest
     */
    public function unless(mixed $value, callable $callback, callable $default = null): static;

    /**
     * Access the config
     *
     * @return ArrayStore
     */
    public function config(): ArrayStore;

    /**
     * Access the headers
     *
     * @return ArrayStore
     */
    public function headers(): ArrayStore;

    /**
     * Access the middleware pipeline
     *
     * @return MiddlewarePipeline
     */
    public function middleware(): MiddlewarePipeline;

    /**
     * Specify a mock client.
     *
     * @param MockClient $mockClient
     * @return \Saloon\Http\PendingRequest
     */
    public function setMockClient(MockClient $mockClient): static;

    /**
     * Get the mock client.
     *
     * @return MockClient|null
     */
    public function getMockClient(): ?MockClient;

    /**
     * Determine if the instance has a mock client
     *
     * @return bool
     */
    public function hasMockClient(): bool;

    /**
     * Access the query parameters
     *
     * @return ArrayStore
     */
    public function query(): ArrayStore;

    /**
     * Build up the request payload.
     *
     * @param \Saloon\Contracts\Connector $connector
     * @param Request $request
     * @param MockClient|null $mockClient
     */
    public function __construct(Connector $connector, Request $request, MockClient $mockClient = null);

    /**
     * Execute the response pipeline.
     *
     * @param Response $response
     * @return Response
     */
    public function executeResponsePipeline(Response $response): Response;

    /**
     * Get the request.
     *
     * @return Request
     */
    public function getRequest(): Request;

    /**
     * Get the connector.
     *
     * @return Connector
     */
    public function getConnector(): Connector;

    /**
     * Get the URL of the request.
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Get the HTTP method used for the request
     *
     * @return Method
     */
    public function getMethod(): Method;

    /**
     * Get the response class used for the request
     *
     * @return string
     */
    public function getResponseClass(): string;

    /**
     * Get the request sender.
     *
     * @return Sender
     */
    public function getSender(): Sender;

    /**
     * Retrieve the body on the instance
     *
     * @return BodyRepository|null
     */
    public function body(): ?BodyRepository;

    /**
     * Get the simulated response payload
     *
     * @return SimulatedResponsePayload|null
     */
    public function getSimulatedResponsePayload(): ?SimulatedResponsePayload;

    /**
     * Set the simulated response payload
     *
     * @param SimulatedResponsePayload|null $simulatedResponsePayload
     * @return $this
     */
    public function setSimulatedResponsePayload(?SimulatedResponsePayload $simulatedResponsePayload): static;

    /**
     * Check if simulated response payload is present.
     *
     * @return bool
     */
    public function hasSimulatedResponsePayload(): bool;

    /**
     * Send the PendingRequest
     *
     * @return Response
     */
    public function send(): Response;

    /**
     * Send the PendingRequest asynchronously
     *
     * @return PromiseInterface
     */
    public function sendAsync(): PromiseInterface;

    /**
     * Create a data object from the response
     *
     * @param \Saloon\Contracts\Response $response
     * @return mixed
     */
    public function createDtoFromResponse(Response $response): mixed;
}
