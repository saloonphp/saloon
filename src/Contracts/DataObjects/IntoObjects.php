<?php

declare(strict_types=1);

namespace Saloon\Contracts\DataObjects;

use Saloon\Http\Response;

interface IntoObjects
{
    /**
     * Handle the creation of the object from Saloon
     *
     * @return array<self>
     */
    public static function fromResponse(Response $response): array;
}
