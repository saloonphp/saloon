<?php

namespace Sammyjo20\Saloon\Http;

class SaloonMock
{
    protected int $statusCode;

    protected array $headers;

    protected string $body;

    public function __construct(int $statusCode, array $headers = [], string|array $body = '')
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = is_array($body) ? json_encode($body) : $body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
