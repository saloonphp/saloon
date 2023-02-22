<?php

declare(strict_types=1);

namespace Saloon\Debugging\Drivers;

use Saloon\Contracts\Response;
use Saloon\Debugging\DebugData;
use Saloon\Contracts\ArrayStore;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Repositories\Body\MultipartBodyRepository;
use Saloon\Contracts\DebuggingDriver as DebuggingDriverContract;

abstract class DebuggingDriver implements DebuggingDriverContract
{
    /**
     * @param \Saloon\Debugging\DebugData $data
     * @param bool $asArray
     * @return array<string, mixed>
     */
    protected function formatData(DebugData $data, bool $asArray = false): array
    {
        $formattedData = [
            ...$this->formatRequestData($data),
            ...$this->formatResponseData($data),
        ];

        if ($asArray === false) {
            return $formattedData;
        }

        return $this->formatDataAsArray($formattedData);
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return array<string, mixed>
     */
    protected function formatRequestData(DebugData $data): array
    {
        return [
            'method' => $data->getMethod(),
            'uri' => $data->getUrl(),
            'request_headers' => $data->getPendingRequest()->headers(),
            'request_query' => $data->getPendingRequest()->query(),
            'request_payload' => $data->getPendingRequest()->body(),
            'sender_config' => $data->getPendingRequest()->config(),
            'classes' => [
                'request' => $data->getRequest()::class,
                'connector' => $data->getConnector()::class,
                'sender' => $data->getSender()::class,
            ],
        ];
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return array<string, mixed>
     */
    protected function formatResponseData(DebugData $data): array
    {
        $response = $data->getResponse();

        if (is_null($response)) {
            return [];
        }

        return [
            'response_status' => $response->status(),
            'response_headers' => $response->headers(),
            'response_body' => $this->formatResponseBody($response),
        ];
    }

    /**
     * Format the response body
     *
     * @param \Saloon\Contracts\Response $response
     * @return mixed
     */
    protected function formatResponseBody(Response $response): mixed
    {
        if (str_contains($response->header('Content-Type') ?? '', 'application/json')) {
            return $response->json();
        }

        return $response->body();
    }

    /**
     * Format the items of the array into arrays
     *
     * @param array $formattedData
     * @return array
     */
    protected function formatDataAsArray(array $formattedData): array
    {
        return array_map(static function (mixed $item) {
            return match (true) {
                $item instanceof ArrayStore, $item instanceof BodyRepository => $item->all(),
                $item instanceof MultipartBodyRepository => $item->toArray(),
                default => $item,
            };
        }, $formattedData);
    }
}
