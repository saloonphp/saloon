<?php

declare(strict_types=1);

namespace Saloon\Contracts\DataObjects;

use Saloon\Http\Response;

interface WithResponse
{
    /**
     * Set the response on the data object.
     *
     * @param Response<mixed> $response
     * @return $this
     */
    public function setResponse(Response $response): static;

    /**
     * Get the response on the data object.
     *
     * @return Response<mixed>
     */
    public function getResponse(): Response;
}
