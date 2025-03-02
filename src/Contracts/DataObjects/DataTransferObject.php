<?php

declare(strict_types=1);

namespace Saloon\Contracts\DataObjects;

use Saloon\Http\Response;

interface DataTransferObject
{
    /**
     * Handle the creation of the object from Saloon
     */
    public static function fromResponse(Response $response): static;
}
