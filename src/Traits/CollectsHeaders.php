<?php

namespace Sammyjo20\Saloon\Traits;

use Illuminate\Support\Arr;

trait CollectsHeaders
{
    /**
     * Headers that have been added, this doesn't include default headers.
     *
     * @var array
     */
    private array $customHeaders = [];

    /**
     * Should we include the default headers when using ->getHeaders()?
     *
     * @var bool
     */
    private bool $includeDefaultHeaders = true;

    /**
     * Default headers.
     *
     * @return array
     */
    public function defaultHeaders(): array
    {
        return [];
    }

    /**
     * Merge headers together into one array.
     *
     * @param mixed ...$headerCollection
     * @return $this
     */
    public function mergeHeaders(array ...$headerCollection): static
    {
        foreach ($headerCollection as $headers) {
            $this->customHeaders = array_merge($this->customHeaders, $headers);
        }

        return $this;
    }

    /**
     * Set the whole headers array.
     *
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers): static
    {
        $this->ignoreDefaultHeaders();

        $this->customHeaders = $headers;

        return $this;
    }

    /**
     * Add an individual header.
     *
     * @param string $header
     * @param $value
     * @return $this
     */
    public function addHeader(string $header, $value): static
    {
        $this->customHeaders[$header] = $value;

        return $this;
    }

    /**
     * Get all headers or filter with a key.
     *
     * @param string|null $key
     * @return array
     */
    public function getHeaders(string $key = null): mixed
    {
        $headerBag = $this->includeDefaultHeaders
            ? array_merge($this->defaultHeaders(), $this->customHeaders)
            : $this->customHeaders;

        if (isset($key)) {
            return Arr::get($headerBag, $key);
        }

        return $headerBag;
    }

    /**
     * Get an individual header
     *
     * @param string $key
     * @return string
     */
    public function getHeader(string $key): string
    {
        return $this->getHeaders($key) ?? '';
    }

    /**
     * Should we ignore the default headers when calling `->getHeaders()`?
     *
     * @return $this
     */
    public function ignoreDefaultHeaders(): static
    {
        $this->includeDefaultHeaders = false;

        return $this;
    }
}
