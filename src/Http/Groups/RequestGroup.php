<?php declare(strict_types=1);

namespace Saloon\Http\Groups;

use Saloon\Http\SaloonConnector;

abstract class RequestGroup
{
    /**
     * Saloon Connector
     *
     * @var SaloonConnector
     */
    protected SaloonConnector $connector;

    /**
     * Constructor
     *
     * @param SaloonConnector $connector
     */
    public function __construct(SaloonConnector $connector)
    {
        $this->connector = $connector;
    }
}
