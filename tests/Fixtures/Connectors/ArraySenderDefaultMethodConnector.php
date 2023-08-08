<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Contracts\Sender;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Tests\Fixtures\Senders\ArraySender;

class ArraySenderDefaultMethodConnector extends Connector
{
    use AcceptsJson;

    /**
     * Define the base url of the api.
     */
    public function resolveBaseUrl(): string
    {
        return apiUrl();
    }

    /**
     * Default Sender
     */
    protected function defaultSender(): Sender
    {
        return new ArraySender;
    }
}
