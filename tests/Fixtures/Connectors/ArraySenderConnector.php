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
     *
     * @var string
     */
    protected string $defaultSender = ArraySender::class;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function defineBaseUrl(): string
    {
        return apiUrl();
    }

    /**
     * Set the sender
     *
     * @param string $defaultSender
     * @return $this
     */
    public function setDefaultSender(string $defaultSender): static
    {
        $this->defaultSender = $defaultSender;

        return $this;
    }
}
