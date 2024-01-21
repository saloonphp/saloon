<?php

declare(strict_types=1);

namespace Saloon\Data;

use JsonSerializable;
use Saloon\Http\Response;
use Saloon\Http\Faking\MockResponse;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class RecordedResponse implements JsonSerializable
{
    /**
     * Constructor
     *
     * @param array<string, mixed> $headers
     */
    public function __construct(
        public int   $statusCode,
        public array $headers = [],
        public mixed $data = null,
    ) {
        //
    }

    /**
     * Create an instance from file contents
     *
     * @throws \JsonException
     */
    public static function fromFile(string $contents): static
    {
        try {
            $fileData = Yaml::parse($contents);
        } catch (ParseException) {
            $fileData = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        }

        $data = $fileData['data'];

        if (isset($fileData['encoding']) && $fileData['encoding'] === 'base64') {
            $data = base64_decode($data);
        }

        return new static(
            statusCode: $fileData['statusCode'],
            headers: $fileData['headers'],
            data: $data
        );
    }

    /**
     * Create an instance from a Response
     */
    public static function fromResponse(Response $response): static
    {
        return new static(
            statusCode: $response->status(),
            headers: $response->headers()->all(),
            data: $response->body(),
        );
    }

    /**
     * Encode the instance to be stored as a file
     */
    public function toFile(): string
    {
        return Yaml::dump($this->toArray());
    }

    /**
     * Create a mock response from the fixture
     */
    public function toMockResponse(): MockResponse
    {
        return new MockResponse($this->data, $this->statusCode, $this->headers);
    }

    /**
     * Convert the recorded response into an array
     *
     * @return array{
     *     statusCode: int,
     *     headers: array<string, mixed>,
     *     data: mixed,
     * }
     */
    public function toArray(): array
    {
        $response = [
            'statusCode' => $this->statusCode,
            'headers' => $this->headers,
            'data' => $this->data,
        ];

        if (mb_check_encoding($response['data'], 'UTF-8') === false) {
            $response['data'] = base64_encode($response['data']);
            $response['encoding'] = 'base64';
        }

        return $response;
    }

    /**
     * Define the JSON object if this class is converted into JSON
     *
     * @return array{
     *     statusCode: int,
     *     headers: array<string, mixed>,
     *     data: mixed,
     * }
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
