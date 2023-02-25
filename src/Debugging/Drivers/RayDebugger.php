<?php

declare(strict_types=1);

namespace Saloon\Debugging\Drivers;

use Saloon\Debugging\DebugData;

class RayDebugger extends DebuggingDriver
{
    public function name(): string
    {
        return 'ray';
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return void
     */
    public function send(DebugData $data): void
    {
        ray($this->formatData($data))->label('Saloon Debugger');
    }
}
