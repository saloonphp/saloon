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
     *
     * @param \Saloon\Contracts\Response $response
     * @return \Saloon\Contracts\Response
     */
    public function executeResponsePipeline(Response $response): Response;

    /**
     * Get the request.
     *
     * @return \Saloon\Contracts\Request
     */
    public function getRequest(): Request;

    /**
     * Get the connector.
     *
     * @return \Saloon\Contracts\Connector
     */
    public function getConnector(): Connector;

    /**
     * Get the URL of the request.
     */
    public function getUrl(): string;

    /**
     * Get the URI for the pending request.
     */
    public function getUri(): UriInterface;

    /**
     * Get the HTTP method used for the request
     */
    public function getMethod(): Method;

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
     * Get the fake response
     *
     * @return \Saloon\Contracts\FakeResponse|null
     */
    public function getFakeResponse(): ?FakeResponse;

    /**
     * Set the fake response
     *
     * @param \Saloon\Contracts\FakeResponse|null $fakeResponse
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

    /**
     *  Set the body repository
     *
     * @return $this
     */
    public function setBody(?BodyRepository $body): static;
}
