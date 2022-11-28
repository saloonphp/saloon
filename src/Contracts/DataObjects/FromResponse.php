<?php

declare(strict_types=1);

namespace Saloon\Contracts\DataObjects;

use Saloon\Contracts\Response;

interface FromResponse
{
    /**
     * Create a new instance from a Saloon response.
     *
     * @param \Saloon\Contracts\Response $response
     * @return static
     */
    public static function fromResponse(Response $response): static;
}
