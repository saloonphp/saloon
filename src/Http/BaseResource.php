<?php

declare(strict_types=1);

namespace Saloon\Http;

class BaseResource
{
    /**
     * Constructor
     */
    public function __construct(readonly protected Connector $connector)
    {
        //
    }
}
