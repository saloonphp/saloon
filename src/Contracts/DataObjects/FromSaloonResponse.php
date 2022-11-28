<?php

namespace Saloon\Contracts\DataObjects;

use Saloon\Contracts\Response;

interface FromSaloonResponse
{
    /**
     * Create a new instance from a Saloon response.
     *
     * @param \Saloon\Contracts\Response $response
     * @return static
     */
    public static function fromSaloonResponse(Response $response): static;
}
