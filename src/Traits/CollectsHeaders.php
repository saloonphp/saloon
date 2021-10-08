<?php

namespace Sammyjo20\Saloon\Traits;

trait CollectsHeaders
{
    protected array $headers = [];

    public function defineHeaders(): array
    {
        return [];
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function setHeader(string $header, $value): self
    {
        $this->headers[$header] = $value;

        return $this;
    }

    public function mergeHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function header(string $header): mixed
    {
        return $this->headers[$header];
    }
}
