<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Enums\Method;
use Psr\Http\Message\UriInterface;
use Saloon\Data\FactoryCollection;
use Psr\Http\Message\RequestInterface;
use Saloon\Contracts\Body\BodyRepository;

/**
 * @internal
 */
interface PendingRequest extends Authenticatable, HasConfig, HasHeaders, HasMiddlewarePipeline, HasMockClient, HasQueryParams, HasDelay
{
    /**
     * Execute the response pipeline.
     */
    public function executeResponsePipeline(Response $response): Response;

    /**
     * Get the request.
     */
    public function getRequest(): Request;

    /**
     * Get the connector.
     */
    public function getConnector(): Connector;

    /**
     * Get the URL of the request.
     */
    public function getUrl(): string;

    /**
     * Set the URL of the PendingRequest
     *
     * Note: This will be combined with the query parameters to create
     * a UriInterface that will be passed to a PSR-7 request.
     *
     * @return $this
     */
    public function setUrl(string $url): static;

    /**
     * Get the URI for the pending request.
     */
    public function getUri(): UriInterface;

    /**
     * Get the HTTP method used for the request
     */
    public function getMethod(): Method;

    /**
     * Set the method of the PendingRequest
     *
     * @return $this
     */
    public function setMethod(Method $method): static;

    /**
     * Get the response class used for the request
     *
     * @return class-string<\Saloon\Contracts\Response>
     */
    public function getResponseClass(): string;

    /**
     * Retrieve the body on the instance
     */
    public function body(): ?BodyRepository;

    /**
     * Set the body repository
     *
     * @return $this
     */
    public function setBody(?BodyRepository $body): static;

    /**
     * Get the fake response
     */
    public function getFakeResponse(): ?FakeResponse;

    /**
     * Set the fake response
     *
     * @return $this
     */
    public function setFakeResponse(?FakeResponse $fakeResponse): static;

    /**
     * Check if a fake response is present
     */
    public function hasFakeResponse(): bool;

    /**
     * Set if the request is going to be sent asynchronously
     *
     * @return $this
     */
    public function setAsynchronous(bool $asynchronous): static;

    /**
     * Check if the request is asynchronous
     */
    public function isAsynchronous(): bool;

    /**
     * Get the factory collection
     */
    public function getFactoryCollection(): FactoryCollection;

    /**
     * Get the PSR-7 request
     */
    public function createPsrRequest(): RequestInterface;
}
