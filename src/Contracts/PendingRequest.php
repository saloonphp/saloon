<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Enums\Method;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\Body\BodyRepository;

interface PendingRequest extends Authenticatable, Conditionable, HasConfig, HasHeaders, HasMiddlewarePipeline, HasMockClient, HasQueryParams
{
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
     * @return mixed
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
