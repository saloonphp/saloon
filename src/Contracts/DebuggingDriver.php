<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Debugging\DebugData;

interface DebuggingDriver
{
    /**
     * Define the debugger name
     */
    public function name(): string;

    /**
     * Determines if the debugging driver can be used
     *
     * E.g if it has the correct dependencies
     */
    public function hasDependencies(): bool;

    /**
     * Send the data to the debugger
     */
    public function send(DebugData $data): void;
}
