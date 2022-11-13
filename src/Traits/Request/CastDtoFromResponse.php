<?php declare(strict_types=1);

namespace Saloon\Traits\Request;

use Saloon\Contracts\SaloonResponse;

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
