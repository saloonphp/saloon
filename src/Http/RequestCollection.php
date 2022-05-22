<?php

namespace Sammyjo20\Saloon\Http;

abstract class RequestCollection
{
    /**
     * @var SaloonConnector
     */
    protected SaloonConnector $connector;

    /**
     * @param SaloonConnector $connector
     */
    public function __construct(SaloonConnector $connector)
    {
        $this->connector = $connector;
    }
}
