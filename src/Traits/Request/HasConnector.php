<?php declare(strict_types=1);

namespace Saloon\Traits\Request;

use Saloon\Http\SaloonConnector;
use Saloon\Exceptions\SaloonInvalidConnectorException;

trait HasConnector
{
    /**
     * The loaded connector used in requests.
     *
     * @var SaloonConnector|null
     */
    private ?SaloonConnector $loadedConnector = null;

    /**
     * Retrieve the loaded connector.
     *
     * @return SaloonConnector
     * @throws SaloonInvalidConnectorException
     */
    public function connector(): SaloonConnector
    {
        return $this->loadedConnector ??= $this->resolveConnector();
    }

    /**
     * Set the loaded connector at runtime.
     *
     * @param SaloonConnector $connector
     * @return $this
     */
    public function setConnector(SaloonConnector $connector): static
    {
        $this->loadedConnector = $connector;

        return $this;
    }

    /**
     * Create a new connector instance.
     *
     * @return SaloonConnector
     * @throws SaloonInvalidConnectorException
     */
    protected function resolveConnector(): SaloonConnector
    {
        if (empty($this->connector) || ! class_exists($this->connector)) {
            throw new SaloonInvalidConnectorException;
        }

        return new $this->connector;
    }
}
