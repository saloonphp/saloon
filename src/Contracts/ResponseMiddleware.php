<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface ResponseMiddleware
{
    /**
     * Register a response middleware
     *
     * @return \Saloon\Http\Response|void
     */
    public function __invoke(Response $response);
}
