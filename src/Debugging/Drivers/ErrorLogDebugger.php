<?php

declare(strict_types=1);

namespace Saloon\Debugging\Drivers;

use Saloon\Debugging\DebugData;

class ErrorLogDebugger extends DebuggingDriver
{
    /**
     * Define the name
     */
    public function name(): string
    {
        return 'error_log';
    }

    /**
     * Check if the debugging driver can be used
     */
    public function hasDependencies(): bool
    {
        return true;
    }

    /**
     * @throws \JsonException
     */
    public function send(DebugData $data): void
    {
        $encoded = json_encode($this->formatData($data), JSON_THROW_ON_ERROR);

        error_log($encoded, LOG_DEBUG);
    }
}
