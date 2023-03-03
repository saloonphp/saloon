<?php

declare(strict_types=1);

namespace Saloon\Contracts\DataObjects;

use Saloon\Contracts\Connector;

interface WithConnector
{
    /**
     * Set the request connector on the data object.
     *
     * @param \Saloon\Contracts\Connector $connector
     * @return $this
     */
    public function setConnector(Connector $connector): static;

    /**
     * Get the request connector on the data object.
     *
     * @return \Saloon\Contracts\Connector
     */
    public function getConnector(): Connector;
}
