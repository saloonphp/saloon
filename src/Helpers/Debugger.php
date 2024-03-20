<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Closure;
use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\VarDumper\VarDumper;

class Debugger
{
    /**
     * Application "Die" handler.
     *
     * Only used for Saloon tests
     */
    public static ?Closure $dieHandler = null;

    /**
     * Debug a request with Symfony Var Dumper
     */
    public static function symfonyRequestDebugger(PendingRequest $pendingRequest, RequestInterface $psrRequest): void
    {
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
    }

    /**
     * Debug a response with Symfony Var Dumper
     */
    public static function symfonyResponseDebugger(Response $response, ResponseInterface $psrResponse): void
    {
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
    }

    /**
     * Kill the application
     *
     * This is a method as it can be easily mocked during tests
     */
    public static function die(): void
    {
        $handler = self::$dieHandler ?? static fn () => exit(1);

        $handler();
    }
}
