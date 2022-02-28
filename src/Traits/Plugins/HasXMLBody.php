<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Exceptions\SaloonTraitExistsException;

trait HasXMLBody
{
    /**
     * Add the required headers to send XML
     *
     * @return void
     * @throws SaloonTraitExistsException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function bootHasXMLBody(): void
    {
        $this->addHeader('Accept', 'application/xml');
        $this->addHeader('Content-Type', 'application/xml');

        $this->addConfig('body', $this->defineXMLBody());
    }

    /**
     * Define your XML body
     *
     * @return string|null
     */
    abstract public function defineXMLBody(): ?string;
}
