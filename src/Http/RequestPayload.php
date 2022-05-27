<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Helpers\ContentBag;

class RequestPayload
{
    /**
     * Request Headers
     *
     * @var ContentBag
     */
    public ContentBag $headers;

    /**
     * Request Query Parameters
     *
     * @var ContentBag
     */
    public ContentBag $queryParameters;

    /**
     * Request Data
     *
     * @var ContentBag
     */
    public ContentBag $data;

    /**
     * Request Config
     *
     * @var ContentBag
     */
    public ContentBag $config;

    /**
     * Request Guzzle Middleware
     *
     * @var ContentBag
     */
    public ContentBag $guzzleMiddleware;

    /**
     * Request Response Interceptors
     *
     * @var ContentBag
     */
    public ContentBag $responseInterceptors;

    public SaloonConnector $connector;

    public SaloonRequest $request;

    // This contains all of the merged in data from the connector and the request.
    // It acts like a data-transfer-object with extra things included.

    // 1. Construct and accept a request
    // 2. Boot or get the connector
    // 3. Pull in the request data from the request + connector
    // 4. Merge into the request payload's data.

    // 1. "boot" the connector and the request, pass in the request payload
    // 2. "boot" all the plugins, pass in the request payload
    // 3. Run the authenticator
    // 4. Run the laravel manager and merge in any data from there...

    // Now You have a complete request payload with everything inside ready to be sent to the manager!

    // Request payload data:
    // - Request, Connector
    // - Headers, Config, Data, GuzzleMiddleware, Response Interceptors

    // ...

    // Inside a "RequestSender", accept a RequestPayload and we can do whatever we want with it

    // Mock client could live on the connector or the request.
}
