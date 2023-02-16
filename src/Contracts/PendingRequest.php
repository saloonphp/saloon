<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Enums\Method;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\Body\BodyRepository;

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
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Get the HTTP method used for the request
     *
     * @return \Saloon\Enums\Method
     */
    public function getMethod(): Method;

    /**
     * Get the response class used for the request
     *
     * @return class-string<\Saloon\Contracts\Response>
     */
    public function getResponseClass(): string;

    /**
     * Get the request sender.
     *
     * @return \Saloon\Contracts\Sender
     */
    public function getSender(): Sender;

    /**
     * Retrieve the body on the instance
     *
     * @return \Saloon\Contracts\Body\BodyRepository|null
     */
    public function body(): ?BodyRepository;

    /**
     * Get the simulated response payload
     *
     * @return \Saloon\Contracts\SimulatedResponsePayload|null
     */
    public function getSimulatedResponsePayload(): ?SimulatedResponsePayload;

    /**
     * Set the simulated response payload
     *
     * @param \Saloon\Contracts\SimulatedResponsePayload|null $simulatedResponsePayload
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
     * @return \Saloon\Contracts\Response
     */
    public function send(): Response;

    /**
     * Send the PendingRequest asynchronously
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function sendAsync(): PromiseInterface;

    /**
     * Create a data object from the response
     *
     * @param \Saloon\Contracts\Response $response
     * @return mixed
     */
    public function createDtoFromResponse(Response $response): mixed;

    /**
     * Set if the request is going to be sent asynchronously
     *
     * @param bool $asynchronous
     * @return $this
     */
    public function setAsynchronous(bool $asynchronous): static;

    /**
     * Check if the request is asynchronous
     *
     * @return bool
     */
    public function isAsynchronous(): bool;
}
