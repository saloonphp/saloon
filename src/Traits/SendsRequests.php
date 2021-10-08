<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Managers\RequestManager;

trait SendsRequests
{
    /**
     * Send the request.
     *
     * @return SaloonResponse
     * @throws \ReflectionException
     */
    public function send(): SaloonResponse
    {
        // Let's pass this job onto the request manager as serializing all the logic to send
        // requests may become cumbersome.

        // 🚀 ... 🌑 ... 💫

        return (new RequestManager($this))->send();
    }

    /**
     * Mock a successful request.
     *
     * @return SaloonResponse
     * @throws \ReflectionException
     */
    public function mockSuccess(): SaloonResponse
    {
        return (new RequestManager($this, Saloon::SUCCESS_MOCK))->send();
    }

    /**
     * Mock a failure request.
     *
     * @return SaloonResponse
     * @throws \ReflectionException
     */
    public function mockFailure(): SaloonResponse
    {
        return (new RequestManager($this, Saloon::FAILURE_MOCK))->send();
    }
}
