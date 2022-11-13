<?php declare(strict_types=1);

namespace Saloon\Http\Groups;

use Saloon\Http\Connector;

abstract class RequestGroup
{
    /**
     * Saloon Connector
     *
     * @var Connector
     */
    protected Connector $connector;

    /**
     * Constructor
     *
     * @param Connector $connector
     */
    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }
}
