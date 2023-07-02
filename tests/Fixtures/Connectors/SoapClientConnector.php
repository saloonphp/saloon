<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Http\Senders\SoapClientSender;

class SoapClientConnector extends Connector
{

    protected string $defaultSender = SoapClientSender::class;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return wsdlUrl();
    }
}
