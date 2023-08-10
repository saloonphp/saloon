<?php

declare(strict_types=1);

namespace Saloon\Traits\Responses;

use Throwable;
use LogicException;
use SimpleXMLElement;
use Saloon\Helpers\ArrayHelpers;
use Illuminate\Support\Collection;
use Saloon\Contracts\FakeResponse;
use Symfony\Component\DomCrawler\Crawler;
use Saloon\Helpers\RequestExceptionHelper;
use Saloon\Contracts\DataObjects\WithResponse;

trait HasResponseHelpers
{
    /**
     * The decoded JSON response.
     *
     * @var array<array-key, mixed>
     */
    protected array $decodedJson;

    /**
     * The decoded XML response.
     */
    protected string $decodedXml;

    /**
     * Denotes if the response has been mocked.
     */
    protected bool $mocked = false;

    /**
     * Denotes if the response has been cached.
     */
    protected bool $cached = false;

    /**
     * The simulated response payload if the response was simulated.
     */
    protected ?FakeResponse $fakeResponse = null;

    /**
     * Get the JSON decoded body of the response as an array or scalar value.
     *
     * @param array-key|null $key
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

        return ArrayHelpers::get($this->decodedJson, $key, $default);
    }

    /**
     * Get the JSON decoded body of the response as an object.
     *
     * @throws \JsonException
     */
    public function object(): object
    {
        return json_decode($this->body(), false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Convert the XML response into a SimpleXMLElement.
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
     */
    public function dto(): mixed
    {
        $request = $this->pendingRequest->getRequest();
        $connector = $this->pendingRequest->getConnector();

        $dataObject = $request->createDtoFromResponse($this) ?? $connector->createDtoFromResponse($this);

        if ($dataObject instanceof WithResponse) {
            $dataObject->setResponse($this);
        }

        return $dataObject;
    }

    /**
     * Convert the response into a DTO or throw a LogicException if the response failed
     *
     * @throws LogicException
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
     *
     * @see https://symfony.com/doc/current/components/dom_crawler.html
     */
    public function dom(): Crawler
    {
        return new Crawler($this->body());
    }

    /**
     * Determine if the request was successful.
     */
    public function successful(): bool
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    /**
     * Determine if the response code was "OK".
     */
    public function ok(): bool
    {
        return $this->status() === 200;
    }

    /**
     * Determine if the response was a redirect.
     */
    public function redirect(): bool
    {
        return $this->status() >= 300 && $this->status() < 400;
    }

    /**
     * Determine if the response indicates a client or server error occurred.
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
     */
    public function clientError(): bool
    {
        return $this->status() >= 400 && $this->status() < 500;
    }

    /**
     * Determine if the response indicates a server error occurred.
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
     */
    public function shouldThrowRequestException(): bool
    {
        $pendingRequest = $this->getPendingRequest();

        return $pendingRequest->getRequest()->shouldThrowRequestException($this) || $pendingRequest->getConnector()->shouldThrowRequestException($this);
    }

    /**
     * Create an exception if a server or client error occurred.
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
     */
    public function __toString(): string
    {
        return $this->body();
    }

    /**
     * Check if the response has been cached
     */
    public function isCached(): bool
    {
        return $this->cached;
    }

    /**
     * Check if the response has been mocked
     */
    public function isMocked(): bool
    {
        return $this->mocked;
    }

    /**
     * Check if the response has been simulated
     */
    public function isFaked(): bool
    {
        return $this->isMocked() || $this->isCached();
    }

    /**
     * Set if a response has been cached or not.
     *
     * @return $this
     */
    public function setCached(bool $value): static
    {
        $this->cached = true;

        return $this;
    }

    /**
     * Set if a response has been mocked or not.
     *
     * @return $this
     */
    public function setMocked(bool $value): static
    {
        $this->mocked = true;

        return $this;
    }

    /**
     * Set the simulated response payload if the response was simulated.
     *
     * @return $this
     */
    public function setFakeResponse(FakeResponse $fakeResponse): static
    {
        $this->fakeResponse = $fakeResponse;

        return $this;
    }

    /**
     * Get the simulated response payload if the response was simulated.
     */
    public function getFakeResponse(): ?FakeResponse
    {
        return $this->fakeResponse;
    }
}
