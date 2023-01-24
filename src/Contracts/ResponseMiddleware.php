<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface ResponseMiddleware
{
    /**
     * Register a response middleware
     *
     * @param \Saloon\Contracts\Response $response
     * @return \Saloon\Contracts\Response|void
     */
    public function __invoke(Response $response);
}
