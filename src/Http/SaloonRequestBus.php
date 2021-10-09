<?php

namespace Sammyjo20\Saloon\Http;

use Illuminate\Support\Collection;
use Sammyjo20\Saloon\Constants\Saloon;

class SaloonRequestBus
{
    /**
     * Chain a bunch of requests. Not asynchronous.
     *
     * @param array $requests
     * @param bool $responseCollection
     * @param string|null $mockType
     * @return mixed
     * @throws \ReflectionException
     */
    public static function chain(array $requests, bool $responseCollection = false, ?string $mockType = null): mixed
    {
        $responseBag = [];

        foreach ($requests as $request) {
            if (! $request instanceof SaloonRequest) {
                continue;
            }

            if (is_null($mockType)) {
                $response = $request->send();
            }

            if ($mockType === Saloon::SUCCESS_MOCK) {
                $response = $request->mockSuccess();
            }

            if ($mockType === Saloon::FAILURE_MOCK) {
                $response = $request->mockFailure();
            }

            $responseBag[] = $response;
        }

        return $responseCollection ? new Collection($responseBag) : $responseBag;
    }

    /**
     * Run all the requests in "success" mocking mode.
     *
     * @param array $requests
     * @param bool $responseCollection
     * @return mixed
     * @throws \ReflectionException
     */
    public static function mockSuccessChain(array $requests, bool $responseCollection = false): mixed
    {
        return self::chain($requests, $responseCollection, Saloon::SUCCESS_MOCK);
    }

    /**
     * Run all the requests in "failure" mocking mode.
     *
     * @param array $requests
     * @param bool $responseCollection
     * @return mixed
     * @throws \ReflectionException
     */
    public static function mockFailureChain(array $requests, bool $responseCollection = false): mixed
    {
        return self::chain($requests, $responseCollection, Saloon::FAILURE_MOCK);
    }
}
