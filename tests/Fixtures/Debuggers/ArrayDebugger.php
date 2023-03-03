<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Debuggers;

use Saloon\Debugging\DebugData;
use Saloon\Debugging\Drivers\DebuggingDriver;

class ArrayDebugger extends DebuggingDriver
{
    /**
     * @var array
     */
    protected array $requests = [];

    /**
     * @var array
     */
    protected array $responses = [];

    /**
     * @return string
     */
    public function name(): string
    {
        return 'array';
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return void
     */
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
     *
     * @return array
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    /**
     * Get response
     *
     * @return array
     */
    public function getResponses(): array
    {
        return $this->responses;
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
        return true;
    }
}
