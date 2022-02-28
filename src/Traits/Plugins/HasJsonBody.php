<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

trait HasJsonBody
{
    /**
     * @return void
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function bootHasJsonBody(): void
    {
        $this->addConfig('json', $this->getData());
    }

    /**
     * Check if the connector has a trait
     *
     * @return bool
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    protected function connectorHasDataTrait(): bool
    {
        return $this->traitExistsOnConnector(HasJsonBody::class);
    }
}
