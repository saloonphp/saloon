<?php

declare(strict_types=1);

namespace Saloon\Debugging;

interface DebuggingDriver
{
    public function name(): string;

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return $this
     */
    public function send(DebugData $data): static;
}
