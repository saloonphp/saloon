<?php

namespace Sammyjo20\Saloon\Http;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;
use Sammyjo20\Saloon\Interfaces\RequestSenderInterface;

abstract class RequestSender implements RequestSenderInterface
{
    public function processResponse(PendingSaloonRequest $pendingRequest, SaloonResponse $saloonResponse, bool $asPromise = false): SaloonResponse|PromiseInterface
    {
        $saloonResponse = $pendingRequest->executeResponsePipeline($saloonResponse);

        // If we are mocking, we should record the request and response on the mock manager,
        // so we can run assertions on the responses.

        if ($pendingRequest->isMocking()) {
            $saloonResponse->setMocked(true);
            $pendingRequest->getMockClient()->recordResponse($saloonResponse);
        }

        if ($asPromise === true) {
            return new FulfilledPromise($saloonResponse);
        }

        return $saloonResponse;
    }
}
