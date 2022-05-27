<?php

namespace Sammyjo20\Saloon\Data;

use Sammyjo20\Saloon\Helpers\ContentBag;

class RequestProperties
{
    /**
     * @param ContentBag $headers
     * @param ContentBag $queryParameters
     * @param ContentBag $data
     * @param ContentBag $config
     * @param ContentBag $guzzleMiddleware
     * @param ContentBag $responseInterceptors
     */
    public function __construct(
        public ContentBag $headers,
        public ContentBag $queryParameters,
        public ContentBag $data,
        public ContentBag $config,
        public ContentBag $guzzleMiddleware,
        public ContentBag $responseInterceptors,
    )
    {
        //
    }
}
