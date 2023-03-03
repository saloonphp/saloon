<?php

declare(strict_types=1);

namespace Saloon\Traits\Responses;

use Saloon\Contracts\Connector;

trait HasConnector
{
    /**
     * The connector used to make the request.
     *
     * @var \Saloon\Contracts\Connector
     */
    protected Connector $connector;

    /**
     * Set the connector on the data object.
     *
     * @param \Saloon\Contracts\Connector $connector
     * @return $this
     */
    public function setConnector(Connector $connector): static
    {
        $this->connector = $connector;

        return $this;
    }

    /**
     * Get the connector on the data object.
     *
     * @return \Saloon\Contracts\Connector
     */
    public function getConnector(): Connector
    {
        return $this->connector;
    }
}
