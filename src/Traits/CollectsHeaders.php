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
    public function mergeHeaders(array ...$headerCollection): self
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
    public function setHeaders(array $headers): self
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
    public function addHeader(string $header, $value): self
    {
        $this->customHeaders[$header] = $value;

        return $this;
    }

    /**
     * Get all headers or filter with a key.
     * Todo: Throw an error if it doesn't exist.
     *
     * @param string|null $key
     * @return array
     */
    public function getHeaders(string $key = null): mixed
    {
        if ($this->includeDefaultHeaders === true) {
            $headerBag = array_merge($this->defaultHeaders(), $this->customHeaders);
        } else {
            $headerBag = $this->customHeaders;
        }

        if (isset($key)) {
            return Arr::get($headerBag, $key);
        }

        return $headerBag;
    }

    /**
     * Get an individual header
     *
     * @param string|null $key
     * @return array
     */
    public function getHeader(string $key): string
    {
        return $this->getHeaders($key);
    }

    /**
     * Should we ignore the default headers when calling `->getHeaders()`?
     *
     * @return $this
     */
    public function ignoreDefaultHeaders(): self
    {
        $this->includeDefaultHeaders = false;

        return $this;
    }
}
