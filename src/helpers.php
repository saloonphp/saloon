<?php

declare(strict_types=1);

namespace Saloon;

use Saloon\Http\Request;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Psr\Http\Message\MessageInterface;
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
 * @template TDebuggable of \Saloon\Http\Request|\Saloon\Http\Connector
 * @param TDebuggable $debuggable
 * @return TDebuggable
 */
function debug(Request|Connector $debuggable, bool $die = true): Request|Connector
{
    $debuggable->debugRequest(function (PendingRequest $pendingRequest, MessageInterface $psrRequest) use ($die) {
        $headers = [];

        foreach ($psrRequest->getHeaders() as $headerName => $value) {
            $headers[$headerName] = implode(';', $value);
        }

        VarDumper::dump('', '"" 🤠 Saloon Request 🤠');

        VarDumper::dump([
            'request' => $pendingRequest->getRequest()::class,
            'method' => $psrRequest->getMethod(),
            'uri' => (string)$psrRequest->getUri(),
            'headers' => $headers,
            'body' => (string)$psrRequest->getBody(),
        ]);

        VarDumper::dump('', '""---------------------');

        if ($die === true) {
            exit(1);
        }
    });

    return $debuggable;
}
