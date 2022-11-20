<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Saloon\Contracts\Response;
use Saloon\Data\RecordedResponse;

class ResponseRecorder
{
    /**
     * Record a response
     *
     * @param \Saloon\Contracts\Response $response
     * @return \Saloon\Data\RecordedResponse
     */
    public static function record(Response $response): RecordedResponse
    {
        return RecordedResponse::fromResponse($response);
    }
}
