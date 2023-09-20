<?php

declare(strict_types=1);

namespace Saloon\Http\PendingRequest;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Contracts\Body\MergeableBody;
use Saloon\Exceptions\PendingRequestException;
use Saloon\Repositories\Body\MultipartBodyRepository;

class MergeBody
{
    /**
     * Merge connector and request body
     *
     * @throws \Saloon\Exceptions\PendingRequestException
     */
    public function __invoke(PendingRequest $pendingRequest): PendingRequest
    {
        $connector = $pendingRequest->getConnector();
        $request = $pendingRequest->getRequest();

        $connectorBody = $connector instanceof HasBody ? $connector->body() : null;
        $requestBody = $request instanceof HasBody ? $request->body() : null;

        if (is_null($connectorBody) && is_null($requestBody)) {
            return $pendingRequest;
        }

        // When both the connector and the request use the `HasBody` interface - we will enforce
        // that they are both of the same type. This means there won't be any confusion when
        // merging.

        if (isset($connectorBody, $requestBody) && ! $connectorBody instanceof $requestBody) {
            throw new PendingRequestException('Connector and request body types must be the same.');
        }

        // We'll start by cloning the request or connector body depending on which
        // one has been set.

        $body = clone $requestBody ?? clone $connectorBody;

        // When both the connector and the request body repositories are mergeable then we
        // will merge them together.

        if (isset($connectorBody, $requestBody) && $connectorBody instanceof MergeableBody && $requestBody instanceof MergeableBody) {
            $repository = clone $connectorBody;

            // We'll clone the request body into the connector body so any properties on the request
            // body will take priority if they are using a keyed array.

            $body = $repository->merge($requestBody->all());
        }

        // Now we'll check if the body is a MultipartBodyRepository. If it is, then we must
        // set the body factory on the instance so the toStream method can create a stream
        // later on in the process.

        if ($body instanceof MultipartBodyRepository) {
            $body->setMultipartBodyFactory($pendingRequest->getFactoryCollection()->multipartBodyFactory);
        }

        $pendingRequest->setBody($body);

        return $pendingRequest;
    }
}
