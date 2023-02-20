<?php

declare(strict_types=1);

namespace Saloon\Debugging\Drivers;

use Saloon\Contracts\DebuggingDriver as DebuggingDriverContract;
use Saloon\Debugging\DebugData;
use Throwable;

abstract class DebuggingDriver implements DebuggingDriverContract
{
    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return array<string, mixed>
     */
    protected function formatData(DebugData $data): array
    {
        return [
            ...$this->formatRequestData($data),
            ...$this->formatResponseData($data),
        ];
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return array<string, mixed>
     */
    protected function formatRequestData(DebugData $data): array
    {
        return [
            'method' => $data->method(),
            'uri' => $data->url(),
            'request_headers' => $data->pendingRequest()->headers(),
            'request_query' => $data->pendingRequest()->query()->all(),
            'request_payload' => $data->pendingRequest()->body()?->all(),
        ];
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return array<string, mixed>
     */
    protected function formatResponseData(DebugData $data): array
    {
        if (is_null($data->response())) {
            return [];
        }

        return [
            'response_status' => $data->response()->status(),
            'response_headers' => $data->response()->headers(),

            // TODO: This should be converted to body/content, and stuff like that.
            'response_body' => $this->formatResponseBody($data),
        ];
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return mixed
     */
    protected function formatResponseBody(DebugData $data): mixed
    {
        return match ($data->response()?->header('Content-Type')) {
            'application/json' => $data->response()->json(),
            'application/xml' => $data->response()->xml(),
            default => (function () use ($data): mixed {
                try {
                    // JSON is the most common.
                    return $data->response()?->json();
                } catch (Throwable) {}

                $xml = $data->response()?->xml();

                return false !== $xml ? $xml : $data->response()?->body();
            })(),
        };
    }
}
