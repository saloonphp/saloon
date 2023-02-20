<?php

declare(strict_types=1);

namespace Saloon\Debugging\Drivers;

use Saloon\Debugging\DebugData;

class SystemLogDebugger extends DebuggingDriver
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
}
