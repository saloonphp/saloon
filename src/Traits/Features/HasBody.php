<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait HasBody
{
    public function bootHasBodyFeature()
    {
        $this->mergeConfig([
            'form_params' => $this->getData(),
        ]);
    }

    /**
     * Check if the connector has a trait
     *
     * @return bool
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    protected function connectorHasTrait(): bool
    {
        return array_key_exists(HasBody::class, class_uses($this->getConnector()));
    }
}
