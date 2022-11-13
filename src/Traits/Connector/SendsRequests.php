<?php declare(strict_types=1);

namespace Saloon\Traits\Connector;

use ReflectionException;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Http\Request;
use Saloon\Actions\SendRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Contracts\SaloonResponse;
use Saloon\Exceptions\SaloonException;

trait SendsRequests
{
    /**
     * Send a request
     *
     * @param Request $request
     * @param MockClient|null $mockClient
     * @param bool $asynchronous
     * @return SaloonResponse|PromiseInterface
     * @throws SaloonException
     * @throws ReflectionException
     */
    public function send(Request $request, MockClient $mockClient = null, bool $asynchronous = false): SaloonResponse|PromiseInterface
    {
        $request->setConnector($this);

        $pendingRequest = $request->createPendingRequest($mockClient);

        return (new SendRequest($pendingRequest, $asynchronous))->execute();
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
        return $this->send($request, $mockClient, true);
    }
}
