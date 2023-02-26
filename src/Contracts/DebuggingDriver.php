<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Debugging\DebugData;

interface DebuggingDriver
{
    /**
     * Define the debugger name
     *
     * @return string
     */
    public function name(): string;

    /**
     * Send the data to the debugger
     *
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return void
     */
    public function send(DebugData $data): void;
}
