<?php

declare(strict_types=1);

namespace Saloon;

use Saloon\Http\Request;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Psr\Http\Message\MessageInterface;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Debug Request
 *
 * Wrap this around a request class to debug the request being sent.
 *
 * @template TDebuggable of \Saloon\Http\Request|\Saloon\Http\Connector
 * @param TDebuggable $debuggable
 * @return TDebuggable
 */
function debugger(Request|Connector $debuggable, bool $die = true): Request|Connector
{
    $debuggable->debugRequest(function (PendingRequest $pendingRequest, MessageInterface $psrRequest) use ($die) {
        $headers = [];

        foreach ($psrRequest->getHeaders() as $headerName => $value) {
            $headers[$headerName] = implode(';', $value);
        }

        VarDumper::dump([
            'request' => $pendingRequest->getRequest()::class,
            'method' => $psrRequest->getMethod(),
            'uri' => (string)$psrRequest->getUri(),
            'headers' => $headers,
            'body' => (string)$psrRequest->getBody(),
        ]);

        if ($die === true) {
            exit(1);
        }
    });

    return $debuggable;
}
