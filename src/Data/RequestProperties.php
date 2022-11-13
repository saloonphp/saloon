<?php declare(strict_types=1);

namespace Saloon\Data;

use Saloon\Helpers\MiddlewarePipeline;

class RequestProperties
{
    /**
     * @param array $headers
     * @param array $queryParameters
     * @param mixed $data
     * @param array $config
     * @param MiddlewarePipeline $middleware
     */
    public function __construct(
        public array              $headers,
        public array              $queryParameters,
        public array              $config,
        public MiddlewarePipeline $middleware,
    ) {
        //
    }
}
