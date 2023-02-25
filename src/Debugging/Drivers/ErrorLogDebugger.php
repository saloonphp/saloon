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
     */
    public function send(DebugData $data): void
    {
        error_log(print_r($this->formatData($data, true), true), LOG_DEBUG);
    }
}
