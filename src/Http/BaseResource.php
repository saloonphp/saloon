<?php

namespace Saloon\Http;

class BaseResource
{
    /**
     * Constructor
     *
     * @param \Saloon\Http\Connector $connector
     */
    public function __construct(readonly protected Connector $connector)
    {
        //
    }
}
