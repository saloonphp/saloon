<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\SaloonRequest;

trait HasXMLBody
{
    /**
     * Add the required headers to send XML
     *
     * @param SaloonRequest $request
     * @return void
     */
    public function bootHasXMLBody(SaloonRequest $request): void
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
