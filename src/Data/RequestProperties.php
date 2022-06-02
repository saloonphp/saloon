<?php

namespace Sammyjo20\Saloon\Data;

class RequestProperties
{
    /**
     * @param array $headers
     * @param array $queryParameters
     * @param array $data
     * @param array $config
     * @param array $guzzleMiddleware
     * @param array $responseInterceptors
     */
    public function __construct(
        public array $headers,
        public array $queryParameters,
        public array $data,
        public array $config,
        public array $guzzleMiddleware,
        public array $responseInterceptors
    ) {
        //
    }
}
