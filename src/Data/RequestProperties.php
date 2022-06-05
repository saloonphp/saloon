<?php

namespace Sammyjo20\Saloon\Data;

use Sammyjo20\Saloon\Helpers\MiddlewarePipeline;

class RequestProperties
{
    /**
     * @param array $headers
     * @param array $queryParameters
     * @param array $data
     * @param array $config
     * @param MiddlewarePipeline $middleware
     */
    public function __construct(
        public array              $headers,
        public array              $queryParameters,
        public array|string       $data,
        public array              $config,
        public MiddlewarePipeline $middleware,
    ) {
        //
    }
}
