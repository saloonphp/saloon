<?php

declare(strict_types=1);

namespace Saloon\Tests\PHPStan\Fixtures;

use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Tests\Fixtures\Data\User;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function PHPStan\Testing\assertType;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\SoloUserRequest;

use Saloon\Tests\Fixtures\Requests\TypedDtoRequest;

$connector = new TestConnector;

assertType(User::class, $connector->send(new TypedDtoRequest)->dto());
assertType(User::class, $connector->send(new TypedDtoRequest)->dtoOrFail());

// The request type survives a variable between sending and reading.
$response = $connector->send(new TypedDtoRequest);
assertType(User::class, $response->dto());

// A solo request sends itself.
assertType('Saloon\Http\Response<Saloon\Tests\Fixtures\Requests\SoloUserRequest>', (new SoloUserRequest)->send());

// Without a typed createDtoFromResponse() or a known request, dto() stays mixed.
assertType('mixed', $connector->send(new UserRequest)->dto());

function readUnknown(Response $response): void
{
    assertType('mixed', $response->dto());
}

/**
 * @param \Saloon\Http\PendingRequest<\Saloon\Tests\Fixtures\Requests\TypedDtoRequest> $pendingRequest
 */
function responseFromPendingRequest(PendingRequest $pendingRequest, ResponseInterface $psrResponse, RequestInterface $psrRequest): void
{
    $response = Response::fromPsrResponse($psrResponse, $pendingRequest, $psrRequest);

    assertType('Saloon\Http\Response<Saloon\Tests\Fixtures\Requests\TypedDtoRequest>', $response);
    assertType(TypedDtoRequest::class, $response->getRequest());
    assertType(User::class, $response->withBufferedBody('')->dto());
}
