<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Managers\RequestManager;

trait SendsRequests
{
    /**
     * Send the request.
     *
     * @return SaloonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonDuplicateHandlerException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidHandlerException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonMissingMockException
     */
    public function send(): SaloonResponse
    {
        // Let's pass this job onto the request manager as serializing all the logic to send
        // requests may become cumbersome.

        // 🚀 ... 🌑 ... 💫

        return (new RequestManager($this))->send();
    }
}
