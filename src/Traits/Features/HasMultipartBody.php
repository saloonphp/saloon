<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait HasMultipartBody
{
    public function bootHasMultipartBodyFeature()
    {
        $this->mergeConfig([
            'multipart' => $this->getData(),
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
        return $this->traitExistsOnConnector(HasMultipartBody::class);
    }
}
