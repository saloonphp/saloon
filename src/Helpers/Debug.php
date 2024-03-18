<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Saloon\Http\PendingRequest;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\VarDumper\VarDumper;

class Debug
{
    public static function dd(PendingRequest $request, RequestInterface $psrRequest, bool $die = true): void
    {
        VarDumper::dump([
            'method' => $psrRequest->getMethod(),
            'uri' => (string)$psrRequest->getUri(),
            'headers' => $psrRequest->getHeaders(),
            'body' => (string)$psrRequest->getBody(),
        ]);

        if ($die === true) {
            exit(1);
        }
    }

    public static function dump(PendingRequest $request, RequestInterface $psrRequest): void
    {
        static::dd($request, $psrRequest, false);
    }
}
