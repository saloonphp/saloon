<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Debugging\DebugData;

interface DebuggingDriver
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return void
     */
    public function send(DebugData $data): void;
}
