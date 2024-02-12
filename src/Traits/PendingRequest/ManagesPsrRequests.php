<?php

declare(strict_types=1);

namespace Saloon\Traits\PendingRequest;

use Saloon\Helpers\URLHelper;
use Psr\Http\Message\UriInterface;
use Saloon\Data\FactoryCollection;
use Psr\Http\Message\RequestInterface;
use Saloon\Contracts\Body\BodyRepository;

trait ManagesPsrRequests
{
    /**
     * The factory collection.
     */
    protected FactoryCollection $factoryCollection;

    /**
     * Get the URI for the pending request.
     */
    public function getUri(): UriInterface
    {
        $uri = $this->factoryCollection->uriFactory->createUri($this->getUrl());

        // We'll parse the existing query parameters from the URL (if they have been defined)
        // and then we'll merge in Saloon's query parameters. Our query parameters will take
        // priority over any that were defined in the URL.

        $existingQuery = URLHelper::parseQueryString($uri->getQuery());

        return $uri->withQuery(
            http_build_query(array_merge($existingQuery, $this->query()->all()))
        );
    }

    /**
     * Get the PSR-7 request
     */
    public function createPsrRequest(): RequestInterface
    {
        $factories = $this->factoryCollection;

        $request = $factories->requestFactory->createRequest(
            method: $this->getMethod()->value,
            uri: $this->getUri(),
        );

        foreach ($this->headers()->all() as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
        }

        if ($this->body() instanceof BodyRepository) {
            $request = $request->withBody($this->body()->toStream($factories->streamFactory));
        }

        // Now we'll run our event hooks on both the connector and request which allows the
        // user to be able to make any final changes to the PSR request if they need to
        // like modifying the URI or adding extra headers.

        $request = $this->connector->handlePsrRequest($request, $this);

        return $this->request->handlePsrRequest($request, $this);
    }

    /**
     * Get the factory collection
     */
    public function getFactoryCollection(): FactoryCollection
    {
        return $this->factoryCollection;
    }
}
