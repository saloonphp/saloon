<?php

namespace Sammyjo20\Saloon\Data;

use Sammyjo20\Saloon\Helpers\Middleware;

class RequestProperties
{
    /**
     * @param array $headers
     * @param array $queryParameters
     * @param array $data
     * @param array $config
     * @param Middleware $middleware
     */
    public function __construct(
        public array $headers,
        public array $queryParameters,
        public array $data,
        public array $config,
        public Middleware $middleware,
    ) {
        //
    }
}
