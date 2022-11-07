<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Contracts;

interface ResponseMiddleware
{
    /**
     * Register a request middleware
     *
     * @param SaloonResponse $response
     * @return SaloonResponse|void
     */
    public function __invoke(SaloonResponse $response);
}
