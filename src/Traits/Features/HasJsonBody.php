<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait HasJsonBody
{
    public function bootHasJsonBodyFeature()
    {
        $this->mergeHeaders([
            'Content-Type' => 'application/json',
        ]);

        $this->mergeConfig([
            'json' => $this->getData(),
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
        return array_key_exists(HasJsonBody::class, class_uses($this->getConnector()));
    }
}
