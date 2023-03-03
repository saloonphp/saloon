<?php

declare(strict_types=1);

namespace Saloon\Debugging\Drivers;

use Saloon\Debugging\DebugData;

class ErrorLogDebugger extends DebuggingDriver
{
    public function name(): string
    {
        return 'error_log';
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return void
     * @throws \JsonException
     */
    public function send(DebugData $data): void
    {
        $encoded = json_encode($this->formatData($data), JSON_THROW_ON_ERROR);

        error_log($encoded, LOG_DEBUG);
    }
}
