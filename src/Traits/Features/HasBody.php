<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait HasBody
{
    /**
     * Define any form body.
     *
     * @return void
     */
    public function bootHasBodyFeature(): void
    {
        $this->mergeConfig([
            'body' => $this->defineBody(),
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

    /**
     * Define the body data that should be sent
     *
     * @return mixed
     */
    public function defineBody(): mixed
    {
        return null;
    }
}
