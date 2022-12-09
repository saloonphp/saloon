<?php

declare(strict_types=1);

namespace Saloon\Traits\Responses;

use Saloon\Exceptions\Request\RequestException;
use Saloon\Helpers\RequestExceptionHelper;
use Throwable;
use SimpleXMLElement;
use Saloon\Helpers\Arr;
use Illuminate\Support\Collection;
use Saloon\Helpers\StatusCodeHelper;
use Symfony\Component\DomCrawler\Crawler;
use Saloon\Contracts\DataObjects\WithResponse;
use Saloon\Exceptions\Request\ClientException;
use Saloon\Exceptions\Request\ServerException;
use Saloon\Http\Faking\SimulatedResponsePayload;

trait HasResponseHelpers
{
    use HasSimulationMethods;

    /**
     * The decoded JSON response.
     *
     * @var array
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
     * @var SimulatedResponsePayload|null
     */
    protected ?SimulatedResponsePayload $simulatedResponsePayload = null;

    /**
     * Get the JSON decoded body of the response as an array or scalar value.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     * @throws \JsonException
     */
    public function json(string $key = null, mixed $default = null): mixed
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
     * @param ...$arguments
     * @return SimpleXMLElement|bool
     */
    public function xml(...$arguments): SimpleXMLElement|bool
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
     * @param $key
     * @return Collection
     * @throws \JsonException
     */
    public function collect($key = null): Collection
    {
        return Collection::make($this->json($key));
    }

    /**
     * Cast the response to a DTO.
     *
     * @return mixed
     */
    public function dto(): mixed
    {
        if ($this->failed()) {
            return null;
        }

        $dataObject = $this->pendingRequest->createDtoFromResponse($this);

        if ($dataObject instanceof WithResponse) {
            $dataObject->setResponse($this);
        }

        return $dataObject;
    }

    /**
     * Parse the HTML or XML body into a Symfony DomCrawler instance.
     *
     * Requires Symfony Crawler (composer require symfony/dom-crawler)
     * @see https://symfony.com/doc/current/components/dom_crawler.html
     *
     * @return Crawler
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

        return $pendingRequest->getRequest()->shouldThrowRequestException($this) || $pendingRequest->getConnector()->shouldThrowRequestException($this);
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
     * @param callable $callback
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
     * Create an exception if a server or client error occurred.
     *
     * @return Throwable|null
     */
    public function toException(): ?Throwable
    {
        if (! $this->failed()) {
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
        if ($this->failed()) {
            throw $this->toException();
        }

        return $this;
    }

    /**
     * Get a header from the response.
     *
     * @param string $header
     * @return string|array|null
     */
    public function header(string $header): string|array|null
    {
        return $this->headers()->get($header);
    }

    /**
     * Close the stream and any underlying resources.
     *
     * @return $this
     * @throws \JsonException
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
