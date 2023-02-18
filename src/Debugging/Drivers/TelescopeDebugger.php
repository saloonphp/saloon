<?php

declare(strict_types=1);

namespace Saloon\Debugging\Drivers;

use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\Watchers\ClientRequestWatcher;
use Saloon\Debugging\DebugData;
use Saloon\Debugging\DebuggingDriver;

class TelescopeDebugger implements DebuggingDriver
{
    public function name(): string
    {
        return 'telescope';
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return $this
     */
    public function send(DebugData $data): static
    {
        Telescope::recordClientRequest($this->formatData($data));

        return $this;
    }

    protected function formatData(DebugData $data): IncomingEntry
    {
        // TODO: Format the $data, and send it appropriately to Telescope.
        // TODO: We should hide sensitive information, like Telescope does by default.

        // Note: Using other keys has a tendency to be deleted by Telescope.
        //       So it's advisable to look at the Telescope ClientRequestWatcher, and see which keys and data it use.
        $formattedData = [
            'method' => $data->method(),
            'uri' => $data->url(),
            'headers' => $data->pendingRequest()->headers()->all(),
            'payload' => $data->pendingRequest()->body()?->all(),
        ];

        if ($data->wasSent()) {
            $formattedData += [
                'response_status' => $data->response()->status(),
                'response_headers' => $data->response()->headers(),

                // TODO: This should be converted to body/content, and stuff like that.
                'response' => $data->response()->json(),
            ];
        }

        return new IncomingEntry($formattedData);
    }
}
