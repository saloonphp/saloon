<?php

declare(strict_types=1);

namespace Saloon\Debugging\Drivers;

use Saloon\Debugging\DebugData;

class SystemLogDebugger implements DebuggingDriver
{
    public function name(): string
    {
        return 'syslog';
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return $this
     */
    public function send(DebugData $data): static
    {
        syslog(LOG_DEBUG, print_r($this->formatData($data), true));

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatData(DebugData $data): array
    {
        // TODO: Format the $data, and send it appropriately to the syslog().

        $formattedData = [
            'method' => $data->method(),
            'uri' => $data->url(),
            'request_headers' => $data->pendingRequest()->headers(),
            'request_query' => $data->pendingRequest()->query()->all(),
            'request_payload' => $data->pendingRequest()->body()?->all(),
        ];

        if ($data->wasSent()) {
            $formattedData += [
                'response_status' => $data->response()->status(),
                'response_headers' => $data->response()->headers(),

                // TODO: This should be converted to body/content, and stuff like that.
                'response_body' => $data->response()->json(),
            ];
        }

        return $formattedData;
    }
}
