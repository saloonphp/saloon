<?php

declare(strict_types=1);

namespace Saloon\Debugging\Drivers;

use Saloon\Debugging\DebugData;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\IncomingEntry;

class TelescopeDebugger extends DebuggingDriver
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
        Telescope::recordClientRequest(IncomingEntry::make($this->formatData($data)));

        return $this;
    }

    protected function formatData(DebugData $data): array
    {
        // TODO: Format the $data, and send it appropriately to Telescope.
        // TODO: We should hide sensitive information, like Telescope does by default.

        $formattedData = parent::formatData($data);

        // Note: Using other keys than the ones Telescope use, has a tendency to be deleted by Telescope.
        //       So it's advisable to look at the Telescope ClientRequestWatcher, and see which keys and data it use.

        $formattedData['headers'] = $formattedData['request_headers'];
        $formattedData['payload'] = $formattedData['request_payload'];

        unset(
            $formattedData['request_headers'],
            $formattedData['request_payload'],
        );

        if (array_key_exists('response_body', $formattedData)) {
            $formattedData['response'] = $formattedData['response_body'];

            unset($formattedData['response_body']);
        }

        return $formattedData;
    }
}
