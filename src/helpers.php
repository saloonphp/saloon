<?php

declare(strict_types=1);

namespace Saloon;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Saloon\Http\Request;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Debug a request being sent
 *
 * Wrap this around a connector or request before sending to debug the
 * raw request data being sent to the HTTP sender. Note that anything
 * added by the sender will not be reflected here.
 *
 * Example usage: debug($connector)->send($request)
 *
 * Requires symfony/var-dumper
 *
 * @template TDebuggable of \Saloon\Http\Request|\Saloon\Http\Connector
 * @param TDebuggable $debuggable
 * @return TDebuggable
 */
function debug(Request|Connector $debuggable, bool $request = true, bool $response = true, bool $die = true): Request|Connector
{
    if ($request === true) {
        $debuggable->debugRequest(function (PendingRequest $pendingRequest, RequestInterface $psrRequest) use ($response, $die) {
            $headers = [];

            foreach ($psrRequest->getHeaders() as $headerName => $value) {
                $headers[$headerName] = implode(';', $value);
            }

            $label = end(explode('\\', $pendingRequest->getRequest()::class));

            VarDumper::dump([
                'request' => $pendingRequest->getRequest()::class,
                'method' => $psrRequest->getMethod(),
                'uri' => (string)$psrRequest->getUri(),
                'headers' => $headers,
                'body' => (string)$psrRequest->getBody(),
            ], 'Saloon Request (' . $label . ') ->');

            if ($response === false && $die === true) {
                exit(1);
            }
        });
    }

    if ($response === true) {
        $debuggable->debugResponse(function (Response $response, ResponseInterface $psrResponse) use ($die) {
            $headers = [];

            foreach ($psrResponse->getHeaders() as $headerName => $value) {
                $headers[$headerName] = implode(';', $value);
            }

            $label = end(explode('\\', $response->getRequest()::class));

            VarDumper::dump([
                'status' => $response->status(),
                'headers' => $headers,
                'body' => $response->body(),
            ], 'Saloon Response (' . $label . ') ->');

            if ($die === true) {
                exit(1);
            }
        });
    }

    return $debuggable;
}
