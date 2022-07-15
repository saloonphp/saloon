<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;
use Sammyjo20\Saloon\Helpers\ReflectionHelper;
use Sammyjo20\Saloon\Http\SaloonConnector;

trait HasConnector
{
    /**
     * Retrieve the loaded connector.
     *
     * @return SaloonConnector
     * @throws SaloonInvalidConnectorException
     * @throws \ReflectionException
     */
    public function getConnector(): SaloonConnector
    {
        return $this->loadedConnector ??= $this->createConnector();
    }

    /**
     * Set the loaded connector at runtime.
     *
     * @param SaloonConnector $connector
     * @return $this
     */
    public function setConnector(SaloonConnector $connector): self
    {
        $this->loadedConnector = $connector;

        return $this;
    }

    /**
     * Create a new connector instance.
     *
     * @return SaloonConnector
     * @throws SaloonInvalidConnectorException
     * @throws \ReflectionException
     */
    protected function createConnector(): SaloonConnector
    {
        if (empty($this->connector) || ! class_exists($this->connector)) {
            throw new SaloonInvalidConnectorException;
        }

        $isValidConnector = ReflectionHelper::isSubclassOf($this->connector, SaloonConnector::class);

        if (! $isValidConnector) {
            throw new SaloonInvalidConnectorException;
        }

        return new $this->connector;
    }
}
