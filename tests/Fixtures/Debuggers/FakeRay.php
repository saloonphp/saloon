<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Debuggers;

use Spatie\Ray\Client;
use Spatie\Ray\Request;

class FakeRay extends Client
{
    protected array $sentRequests = [];

    public function serverIsAvailable(): bool
    {
        return true;
    }

    public function send(Request $request): void
    {
        $requestProperties = $request->toArray();

        $this->sentRequests[] = $requestProperties;
    }

    public function getSentRequests(): array
    {
        return $this->sentRequests;
    }
}
