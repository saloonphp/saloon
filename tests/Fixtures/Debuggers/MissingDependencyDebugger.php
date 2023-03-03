<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Debuggers;

use Saloon\Debugging\DebugData;
use Saloon\Debugging\Drivers\DebuggingDriver;

class MissingDependencyDebugger extends DebuggingDriver
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'missingDependency';
    }

    /**
     * Determines if the debugging driver can be used
     *
     * E.g if it has the correct dependencies
     *
     * @return bool
     */
    public function hasDependencies(): bool
    {
        return false;
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return void
     */
    public function send(DebugData $data): void
    {
        //
    }
}
