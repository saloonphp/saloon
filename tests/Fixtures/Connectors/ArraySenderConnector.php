<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Tests\Fixtures\Senders\ArraySender;

class ArraySenderConnector extends Connector
{
    use AcceptsJson;

    /**
     * Define the default sender class
     */
    protected string $defaultSender = ArraySender::class;

    /**
     * Define the base url of the api.
     */
    public function resolveBaseUrl(): string
    {
        return apiUrl();
    }

    /**
     * Set the sender
     *
     * @return $this
     */
    public function setDefaultSender(string $defaultSender): static
    {
        $this->defaultSender = $defaultSender;

        return $this;
    }
}
