<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Http\Response;
use Saloon\Enums\PipeOrder;
use Saloon\Http\PendingRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\VarDumper\VarDumper;

trait HasDebugging
{
    /**
     * Register a request debugger
     *
     * Leave blank for a default debugger (requires symfony/var-dump)
     *
     * @param callable(\Saloon\Http\PendingRequest, \Psr\Http\Message\RequestInterface): void|null $onRequest
     * @return $this
     */
    public function debugRequest(?callable $onRequest = null, bool $die = false): static
    {
        // When the user has not specified a callable to debug with, we will use this default
        // debugging driver. This will use symfony/var-dumper to display a nice output to
        // the user's screen of the request.

        $onRequest ??= static function (PendingRequest $pendingRequest, RequestInterface $psrRequest) {
            $headers = [];

            foreach ($psrRequest->getHeaders() as $headerName => $value) {
                $headers[$headerName] = implode(';', $value);
            }

            $className = explode('\\', $pendingRequest->getRequest()::class);
            $label = end($className);

            VarDumper::dump([
                'connector' => $pendingRequest->getConnector()::class,
                'request' => $pendingRequest->getRequest()::class,
                'method' => $psrRequest->getMethod(),
                'uri' => (string)$psrRequest->getUri(),
                'headers' => $headers,
                'body' => (string)$psrRequest->getBody(),
            ], 'Saloon Request (' . $label . ') ->');
        };

        // Register the middleware - we will use PipeOrder::FIRST to ensure that the response
        // is shown before it is modified by the user's middleware.

        $this->middleware()->onRequest(
            callable: static function (PendingRequest $pendingRequest) use ($onRequest, $die): void {
                $onRequest($pendingRequest, $pendingRequest->createPsrRequest());

                if ($die) {
                    exit(1);
                }
            },
            order: PipeOrder::LAST
        );

        return $this;
    }

    /**
     * Register a response debugger
     *
     * Leave blank for a default debugger (requires symfony/var-dump)
     *
     * @param callable(\Saloon\Http\Response, \Psr\Http\Message\ResponseInterface): void|null $onResponse
     * @return $this
     */
    public function debugResponse(?callable $onResponse = null, bool $die = false): static
    {
        // When the user has not specified a callable to debug with, we will use this default
        // debugging driver. This will use symfony/var-dumper to display a nice output to
        // the user's screen of the response.

        $onResponse ??= static function (Response $response, ResponseInterface $psrResponse) {
            $headers = [];

            foreach ($psrResponse->getHeaders() as $headerName => $value) {
                $headers[$headerName] = implode(';', $value);
            }

            $className = explode('\\', $response->getRequest()::class);
            $label = end($className);

            VarDumper::dump([
                'status' => $response->status(),
                'headers' => $headers,
                'body' => $response->body(),
            ], 'Saloon Response (' . $label . ') ->');
        };

        // Register the middleware - we will use PipeOrder::FIRST to ensure that the response
        // is shown before it is modified by the user's middleware.

        $this->middleware()->onResponse(
            callable: static function (Response $response) use ($onResponse, $die): void {
                $onResponse($response, $response->getPsrResponse());

                if ($die) {
                    exit(1);
                }
            },
            order: PipeOrder::FIRST
        );

        return $this;
    }

    /**
     * Dump a pretty output of the request and response.
     *
     * This is useful if you would like to see the request right before it is sent
     * to inspect the body and URI to ensure it is correct. You can also inspect
     * the raw response as it comes back.
     *
     * Note that any changes made to the PSR request by the sender will not be
     * reflected by this output.
     *
     * Requires symfony/var-dumper
     */
    public function debug(bool $die = false): static
    {
        return $this->debugRequest()->debugResponse(die: $die);
    }
}
