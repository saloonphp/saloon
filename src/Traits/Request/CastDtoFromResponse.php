<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\Request;

use Sammyjo20\Saloon\Contracts\SaloonResponse;

trait CastDtoFromResponse
{
    /**
     * Cast the response to a DTO.
     *
     * @param SaloonResponse $response
     * @return mixed
     */
    public function createDtoFromResponse(SaloonResponse $response): mixed
    {
        return null;
    }
}
