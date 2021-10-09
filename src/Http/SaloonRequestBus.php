<?php

namespace Sammyjo20\Saloon\Http;

use Illuminate\Support\Collection;

class SaloonRequestBus
{
    /**
     * Chain a bunch of requests. Not asynchronous.
     *
     * @param array $requests
     * @param bool $responseCollection
     * @return mixed
     * @throws \ReflectionException
     */
    public static function chain(array $requests, bool $responseCollection = false): mixed
    {
        $responseBag = [];

        foreach ($requests as $request) {
            if (! $request instanceof SaloonRequest) {
                continue;
            }

            $response = $request->send();

            $responseBag[] = $response;
        }

        return $responseCollection ? new Collection($responseBag) : $responseBag;
    }
}
