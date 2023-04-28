<?php

declare(strict_types=1);

namespace Saloon\Traits\Responses;

use Throwable;
use LogicException;
use SimpleXMLElement;
use Saloon\Helpers\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;
use Saloon\Helpers\RequestExceptionHelper;
use Saloon\Contracts\DataObjects\WithResponse;
use Saloon\Contracts\SimulatedResponsePayload;

trait HasResponseHelpers
{
    use HasSimulationMethods;

    /**
     * The decoded JSON response.
     *
     * @var array<array-key, mixed>
     */
    protected array $decodedJson;

    /**
     * The decoded XML response.
     *
     * @var string
     */
    protected string $decodedXml;

    /**
     * Denotes if the response has been mocked.
     *
     * @var bool
     */
    protected bool $mocked = false;

    /**
     * Denotes if the response has been cached.
     *
     * @var bool
     */
    protected bool $cached = false;

    /**
     * The simulated response payload if the response was simulated.
     *
     * @var \Saloon\Contracts\SimulatedResponsePayload|null
     */
    protected ?SimulatedResponsePayload $simulatedResponsePayload = null;

    /**
     * Get the JSON decoded body of the response as an array or scalar value.
     *
     * @param array-key|null $key
     * @param mixed|null $default
     * @return ($key is null ? array<array-key, mixed> : mixed)
     * @throws \JsonException
     */
    public function json(string|int|null $key = null, mixed $default = null): mixed
    {
        if (! isset($this->decodedJson)) {
            $this->decodedJson = json_decode($this->body(), true, 512, JSON_THROW_ON_ERROR);
        }

        if (is_null($key)) {
            return $this->decodedJson;
        }

        return Arr::get($this->decodedJson, $key, $default);
    }

    /**
     * Get the JSON decoded body of the response as an object.
     *
     * @return object
     * @throws \JsonException
     */
    public function object(): object
    {
        return json_decode($this->body(), false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Convert the XML response into a SimpleXMLElement.
     *
     * @param mixed ...$arguments
     * @return \SimpleXMLElement|bool
     */
    public function xml(mixed ...$arguments): SimpleXMLElement|bool
    {
        if (! isset($this->decodedXml)) {
            $this->decodedXml = $this->body();
        }

        return simplexml_load_string($this->decodedXml, ...$arguments);
    }

    /**
     * Get the JSON decoded body of the response as a collection.
     *
     * Requires Laravel Collections (composer require illuminate/collections)
     * @see https://github.com/illuminate/collections
     *
     * @param array-key|null $key
     * @return \Illuminate\Support\Collection<array-key, mixed>
     * @throws \JsonException
     */
    public function collect(string|int|null $key = null): Collection
    {
        $data = $this->json($key);

        if (is_null($data)) {
            return Collection::empty();
        }

        if (is_array($data)) {
            return Collection::make($data);
        }

        return Collection::make([$data]);
    }

    /**
     * Cast the response to a DTO.
     *
     * @return mixed
     */
    public function dto(): mixed
    {
        $dataObject = $this->pendingRequest->createDtoFromResponse($this);

        if ($dataObject instanceof WithResponse) {
            $dataObject->setResponse($this);
        }

        return $dataObject;
    }

    /**
     * Convert the response into a DTO or throw a LogicException if the response failed
     *
     * @throws LogicException
     * @return mixed
     */
    public function dtoOrFail(): mixed
    {
        if ($this->failed()) {
            throw new LogicException('Unable to create data transfer object as the response has failed.', 0, $this->toException());
        }

        return $this->dto();
    }

    /**
     * Parse the HTML or XML body into a Symfony DomCrawler instance.
     *
     * Requires Symfony Crawler (composer require symfony/dom-crawler)
     * @see https://symfony.com/doc/current/components/dom_crawler.html
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function dom(): Crawler
    {
        return new Crawler($this->body());
    }

    /**
     * Determine if the request was successful.
     *
     * @return bool
     */
    public function successful(): bool
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    /**
     * Determine if the response code was "OK".
     *
     * @return bool
     */
    public function ok(): bool
    {
        return $this->status() === 200;
    }

    /**
     * Determine if the response was a redirect.
     *
     * @return bool
     */
    public function redirect(): bool
    {
        return $this->status() >= 300 && $this->status() < 400;
    }

    /**
     * Determine if the response indicates a client or server error occurred.
     *
     * @return bool
     */
    public function failed(): bool
    {
        $pendingRequest = $this->getPendingRequest();

        $hasRequestFailed = $pendingRequest->getRequest()->hasRequestFailed($this) || $pendingRequest->getConnector()->hasRequestFailed($this);

        if ($hasRequestFailed === true) {
            return true;
        }

        return $this->serverError() || $this->clientError();
    }

    /**
     * Determine if the response indicates a client error occurred.
     *
     * @return bool
     */
    public function clientError(): bool
    {
        return $this->status() >= 400 && $this->status() < 500;
    }

    /**
     * Determine if the response indicates a server error occurred.
     *
     * @return bool
     */
    public function serverError(): bool
    {
        return $this->status() >= 500;
    }

    /**
     * Execute the given callback if there was a server or client error.
     *
     * @param callable($this): (void) $callback
     * @return $this
     */
    public function onError(callable $callback): static
    {
        if ($this->failed()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Determine if the response should throw a request exception.
     *
     * @return bool
     */
    public function shouldThrowRequestException(): bool
    {
        $pendingRequest = $this->getPendingRequest();

        return $pendingRequest->getRequest()->shouldThrowRequestException($this) || $pendingRequest->getConnector()->shouldThrowRequestException($this);
    }

    /**
     * Create an exception if a server or client error occurred.
     *
     * @return \Throwable|null
     */
    public function toException(): ?Throwable
    {
        if (! $this->shouldThrowRequestException()) {
            return null;
        }

        return $this->createException();
    }

    /**
     * Create the request exception
     *
     * @return \Throwable
     */
    protected function createException(): Throwable
    {
        $pendingRequest = $this->getPendingRequest();
        $senderException = $this->getSenderException();

        // We'll first check if the user has defined their own exception handlers.
        // We'll prioritise the request over the connector.

        $exception = $pendingRequest->getRequest()->getRequestException($this, $senderException) ?? $pendingRequest->getConnector()->getRequestException($this, $senderException);

        if ($exception instanceof Throwable) {
            return $exception;
        }

        // Otherwise, we'll throw our own request.

        return RequestExceptionHelper::create($this, $senderException);
    }

    /**
     * Throw an exception if a server or client error occurred.
     *
     * @return $this
     * @throws \Throwable
     */
    public function throw(): static
    {
        if ($this->shouldThrowRequestException()) {
            throw $this->toException();
        }

        return $this;
    }

    /**
     * Get a header from the response.
     *
     * @param string $header
     * @return string|array<array-key, mixed>|null
     */
    public function header(string $header): string|array|null
    {
        return $this->headers()->get($header);
    }

    /**
     * Close the stream and any underlying resources.
     *
     * @return $this
     */
    public function close(): static
    {
        $this->stream()->close();

        return $this;
    }

    /**
     * Get the body of the response.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->body();
    }
}
