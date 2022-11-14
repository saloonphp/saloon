<?php declare(strict_types=1);

namespace Saloon\Traits\Connector;

use GuzzleHttp\Promise\PromiseInterface;
use ReflectionException;
use Saloon\Contracts\Response;
use Saloon\Exceptions\SaloonException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Request;

trait SendsRequests
{
    /**
     * Send a request
     *
     * @param Request $request
     * @param MockClient|null $mockClient
     * @return Response
     * @throws ReflectionException
     * @throws \Saloon\Exceptions\InvalidConnectorException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     * @throws \Saloon\Exceptions\PendingRequestException
     */
    public function send(Request $request, MockClient $mockClient = null): Response
    {
        $request->setConnector($this);

        return $request->createPendingRequest($mockClient)->send();
    }

    /**
     * Send a request asynchronously
     *
     * @param Request $request
     * @param MockClient|null $mockClient
     * @return PromiseInterface
     * @throws ReflectionException
     * @throws SaloonException
     */
    public function sendAsync(Request $request, MockClient $mockClient = null): PromiseInterface
    {
        $request->setConnector($this);

        return $request->createPendingRequest($mockClient)->sendAsync();
    }
}
