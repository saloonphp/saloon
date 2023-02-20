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
     * @return $this
     */
    public function send(DebugData $data): static
    {
        error_log(print_r($this->formatData($data), true), LOG_DEBUG);

        return $this;
    }
}
