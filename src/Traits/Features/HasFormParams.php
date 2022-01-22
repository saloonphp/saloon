<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait HasFormParams
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
    protected function connectorHasDataTrait(): bool
    {
        return array_key_exists(HasFormParams::class, class_uses($this->getConnector()));
    }
}
