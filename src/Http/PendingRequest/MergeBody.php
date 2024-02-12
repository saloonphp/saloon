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

        $connectorBody = $connector instanceof HasBody ? clone $connector->body() : null;
        $requestBody = $request instanceof HasBody ? clone $request->body() : null;

        if (is_null($connectorBody) && is_null($requestBody)) {
            return $pendingRequest;
        }

        // When both the connector and the request use the `HasBody` interface - we will enforce
        // that they are both of the same type. This means there won't be any confusion when
        // merging.

        if (isset($connectorBody, $requestBody) && ! $connectorBody instanceof $requestBody) {
            throw new PendingRequestException('Connector and request body types must be the same.');
        }

        // Now we'll look at both the request body and the connector body. If the request
        // body is null (not set) then we will use the connector body. If both are set
        // then the request body will still be preferred.

        $body = $requestBody ?? $connectorBody;

        // When both the connector and the request body repositories are mergeable then we
        // will merge them together.

        if (isset($connectorBody, $requestBody) && $connectorBody instanceof MergeableBody && $requestBody instanceof MergeableBody) {
            // We'll merge the request body into the connector body so any properties on the request
            // body will take priority if they are using a keyed array.

            $body = $connectorBody->merge($requestBody->all());
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
