<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Debuggers;

use Saloon\Debugging\DebugData;
use Saloon\Debugging\Drivers\DebuggingDriver;

class ArrayDebugger extends DebuggingDriver
{
    
    protected array $requests = [];

    
    protected array $responses = [];

    
    public function name(): string
    {
        return 'array';
    }

    
    public function send(DebugData $data): void
    {
        if ($data->wasNotSent()) {
            $this->requests[] = $this->formatData($data);
        }

        if ($data->wasSent()) {
            $this->responses[] = $this->formatData($data);
        }
    }

    /**
     * Get request
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    /**
     * Get response
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    /**
     * Determines if the debugging driver can be used
     *
     * E.g if it has the correct dependencies
     */
    public function hasDependencies(): bool
    {
        return true;
    }
}
