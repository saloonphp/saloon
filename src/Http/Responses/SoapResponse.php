<?php

declare(strict_types=1);

namespace Saloon\Http\Responses;

use Throwable;
use SoapClient;
use Saloon\Traits\Macroable;
use Saloon\Contracts\Request;
use Saloon\Repositories\ArrayStore;
use Saloon\Contracts\PendingRequest;
use Saloon\Traits\Responses\HasResponseHelpers;
use Saloon\Contracts\Response as ResponseContract;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;

class SoapResponse implements ResponseContract
{
    use Macroable;
    use HasResponseHelpers;

    /**
     * Create a new response instance.
     *
     * @param \SoapClient $client
     * @param mixed $response
     * @param PendingRequest $pendingRequest
     * @param \Throwable|null $senderException
     */
    public function __construct(protected SoapClient $client, protected mixed $response, protected PendingRequest $pendingRequest, protected ?Throwable $senderException = null)
    {
    }

    /**
     * Get the headers from the response.
     *
     * @return \Saloon\Contracts\ArrayStore
     */
    public function headers(): ArrayStoreContract
    {
        $responseHeaders = $this->client->__getLastResponseHeaders();
        preg_match_all('/(.*?):\s(.*?)\r\n/', $responseHeaders, $matches);

        $headers = [];
        if (count($matches) === 3) {
            $headers = array_combine($matches[1], $matches[2]);
        }

        return new ArrayStore($headers);
    }

    /**
     * Get the pending request that created the response.
     *
     * @return \Saloon\Contracts\PendingRequest
     */
    public function getPendingRequest(): PendingRequest
    {
        return $this->pendingRequest;
    }

    /**
     * Get the body of the response as string.
     *
     * @return string
     */
    public function body(): string
    {
        return (string) json_encode($this->response);
    }

    /**
     * Get the status code of the response.
     *
     * @return int
     */
    public function status(): int
    {
        return 200;
    }

    /**
     * Get the original request that created the response.
     *
     * @return \Saloon\Contracts\Request
     */
    public function getRequest(): Request
    {
        return $this->pendingRequest->getRequest();
    }

    /**
     * Get the original sender exception
     *
     * @return \Throwable|null
     */
    public function getSenderException(): ?Throwable
    {
        return $this->senderException;
    }
}
