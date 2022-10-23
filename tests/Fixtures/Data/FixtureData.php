<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Data;

use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Sammyjo20\Saloon\Http\MockResponse;

class FixtureData implements JsonSerializable
{
    // Todo: Consider adding more to the mock response like host etc

    /**
     * Constructor
     *
     * @param int $statusCode
     * @param array $headers
     * @param mixed $data
     */
    public function __construct(
        public int $statusCode,
        public array $headers = [],
        public mixed $data = null,
    )
    {
        //
    }

    /**
     * Create an instance from file contents
     *
     * @param string $contents
     * @return static
     * @throws \JsonException
     */
    public static function fromFile(string $contents): static
    {
        $fileData = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        return new static(
            statusCode: $fileData['statusCode'],
            headers: $fileData['headers'],
            data: $fileData['data']
        );
    }

    /**
     * Create an instance from a Guzzle response
     *
     * @param ResponseInterface $response
     * @return static
     */
    public static function fromGuzzleResponse(ResponseInterface $response): static
    {
        return new static(
            statusCode: $response->getStatusCode(),
            headers: $response->getHeaders(),
            data: (string)$response->getBody(),
        );
    }

    /**
     * Encode the instance to be stored as a file
     *
     * @return string
     * @throws \JsonException
     */
    public function toFile(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }

    /**
     * Create a mock response from the fixture
     *
     * @return MockResponse
     */
    public function toMockResponse(): MockResponse
    {
        return new MockResponse($this->data, $this->statusCode, $this->headers);
    }

    /**
     * Define the JSON object if this class is converted into JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'statusCode' => $this->statusCode,
            'headers' => $this->headers,
            'data' => $this->data,
        ];
    }
}
