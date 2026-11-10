<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Http\Response;

interface ResponseMiddleware
{
    /**
     * Register a response middleware
     *
     * @param Response<mixed> $response
     * @return Response<mixed>|void
     */
    public function __invoke(Response $response);
}
