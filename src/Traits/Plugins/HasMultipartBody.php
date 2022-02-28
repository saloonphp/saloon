<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

trait HasMultipartBody
{
    /**
     * @return void
     */
    public function bootHasMultipartBody(): void
    {
        $this->addConfig('multipart', $this->getData());
    }

    /**
     * Check if the connector has a trait
     *
     * @return bool
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    protected function connectorHasDataTrait(): bool
    {
        return $this->traitExistsOnConnector(HasMultipartBody::class);
    }
}
