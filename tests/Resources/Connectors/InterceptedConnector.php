<?php

namespace Sammyjo20\Saloon\Tests\Resources\Connectors;

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Traits\Features\AcceptsJson;
use Sammyjo20\Saloon\Traits\Features\DisablesSSLVerification;
use Sammyjo20\Saloon\Traits\Features\HasJsonBody;
use Sammyjo20\Saloon\Traits\Features\WithDebugData;

class InterceptedConnector extends SaloonConnector
{
    use AcceptsJson;
    use DisablesSSLVerification;

    public function defineBaseUrl(): string
    {
        return apiUrl();
    }

    public function interceptRequest(SaloonRequest $request): SaloonRequest
    {
        $request->addHeader('X-Connector-Name', 'Interceptor');

        return $request;
    }

    public function interceptResponse(SaloonRequest $request, SaloonResponse $response): SaloonResponse
    {
        $response->throw();

        return $response;
    }
}
